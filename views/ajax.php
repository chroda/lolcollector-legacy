<?php

require_once(__CONTROLLERS_DIR__.'FakeUser.php');
require_once(__CONTROLLERS_DIR__.'RiotAPI.php');
$api = new RiotAPI();

$_response=Array();
if(isset($_GET['action'])){
  extract($_POST);
  switch($_GET['action']):
    case 'signup':
    switch($_GET['subject']){
      case 'summoner':
      // 1 - ok
      // 2 - já cadastrado
      // 3 - não existe
      $signal=1;
      $summoner = removeAccents(strip_tags($_POST['name']));
      foreach ($db->users as $user) {
        if($user->username === $summoner){ $signal=2; }
      }
      $_response['signup']['username'] = $summoner;
      $_response['signup']['summoner'] = $signal;
      break;
      case 'validate':
      $summoner = removeAccents(strip_tags($_POST['name']));
      $server = $_POST['server'];
      $password = strip_tags($_POST['password']);
      $passwordConfirm = strip_tags($_POST['passwordConfirm']);
      $email = $_POST['email'];
      $emailConfirm	= $_POST['emailConfirm'];
      $sex = $_POST['sex'];
      foreach ($db->users as $user) {
        if($user->username === removeAccents($summoner)) {
          $_response['signup']['validate']['failed']['username'	] = 'Usuário já existe.';
        }
      }
      if(strlen($password)<6){
        $_response['signup']['validate']['failed']['password'] = 'Senha menor do que 6 caracteres.'	;
      }
      if($password !== $passwordConfirm){
        $_response['signup']['validate']['failed']['password'] = 'Senhas não conferem.';
      }
      if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $_response['signup']['validate']['failed']['email'] = 'Email inválido.';
      }
      if($email !== $emailConfirm){
        $_response['signup']['validate']['failed']['email'] = 'Emails não conferem.';
      }
      if(!isset($_response['signup']['validate']['failed'])){
        $maxId = 0;
        foreach ($db->users as $user) {
          $maxId = $user->id > $maxId ? $user->id: $maxId;
        }
        $summoner = json_decode(file_get_contents("https://br1.api.riotgames.com/lol/summoner/v3/summoners/by-name/{$summoner}?api_key=".__APP_RIOTAPI_KEY__));
        // $summoner = (object)[
        //   'id' => 1765464,
        //   'accountId' => 1697425,
        //   'name' => 'chroda',
        //   'profileIconId' => 552,
        //   'revisionDate' => 1522129054000,
        //   'summonerLevel' => 54
        // ];
        $dataset = (object)[
          'id' => ++$maxId,
          'riot_id' => $summoner->id,
          'riot_level'	=>$summoner->summonerLevel,
          'name' => $summoner->name,
          'username' => removeAccents(strtolower($summoner->name)),
          'password' => ($password),
          'server' => $server,
          'serverFullname' => 'Brasil',
          'email' => $email,
          'sex' => (int)$sex,
          "champions" => [],
          "champions_skins" => [],
        ];
        $getDb = json_decode(file_get_contents("db_users.json"));
        if(empty($getDb)){ $getDb = new StdClass(); }
        $id = (string) $dataset->id;
        $getDb->$id = $dataset;
        file_put_contents("db_users.json",json_encode($getDb,JSON_PRETTY_PRINT));
        $_SESSION['user']['authenticated']['id'] = $dataset->id;
        $_response['signup']['validate']['success'] = removeAccents(strtolower($summoner->name));
      }
      break;//switch($_GET['subject'])
    }
    break;//signup
    case 'own-all-champions':
    $user = new User($user_id);
    foreach ($champions as $champion){
      $user->addChampion($champion->id);
    }
    break;
    case 'not-own-all-champions':
    $user = new User($user_id);
    if($user->removeAllChampion()){
      $user->removeAllChampionSkin();
    }
    break;
    case 'own-champion':
    $user = new User($user_id);
    $user->addChampion($champion_id);
    break;
    case 'not-own-champion':
    $user = new User($user_id);
    $user->removeChampion($champion_id);
    foreach ($champions as $champion){
      if($champion->id == $champion_id){
        foreach ($champion->skins as $skin){
          $user->removeChampionSkin($skin->id);
        }
        die;
      }
    }
    break;
    case 'own-skinchampion':
    $user = new User($user_id);
    $user->addChampionSkin($skin_id);
    break;
    case 'not-own-skinchampion':
    $user = new User($user_id);
    $user->removeChampionSkin($skin_id);
    break;
    case 'mail':
    extract($_POST);
    $contactName    = ucfirst(strtolower($contactName));
    $contactEmail   = strtolower($contactEmail);
    $contactMessage = strip_tags($contactMessage);
    // Validating
    if((empty($contactName))||($contactName=='Nome')||($contactName=='Name')){
      $_response['errors'][] = 'name';
    }
    if(!filter_var($contactEmail, FILTER_VALIDATE_EMAIL)){
      $_response['errors'][] = 'email';
    }
    if((empty($contactMessage))||($contactMessage=='Mensagem')||($contactMessage=='Message')){
      $_response['errors'][] = 'message';
    }

    $addressee ='chroda@chroda.com.br';
    $subject ='Mail from ChrodaAdventures';
    $body = '<html><head><title>Mail from ChrodaAdventures</title></head><body><fieldset><legend align="center"><strong>'.$contactName.'</strong><br/><small>'.$contactEmail.'</small></legend><p>'.$contactMessage.'</p></fieldset></body></html>';

    $headers   = array();
    $headers[] = "MIME-Version: 1.0";
    $headers[] = "Content-type: text/html; charset=utf-8";
    $headers[] = "From: Contact made by $contactName <$contactEmail>";
    $headers[] = "Reply-To: Christian Marcell \"Chroda\" <$addressee>";
    $headers[] = "Subject: {$subject}";
    $headers[] = "X-Mailer: PHP/".phpversion();

    if(empty($_response['errors'])){
      mail($addressee,$subject,$body,implode("\r\n", $headers));
    }
    break;
  endswitch;
}
print json_encode( $_response );
exit(0);
?>
