<?php

final class User{
	private $db;
	public function __construct($id=false){
		global $db;
		$this->db = new stdClass;
		$this->db->users = $db->users;
		if(is_numeric($id)){
			$this->db->users[$id];
		}
	}

	public function authenticate($username,$password){
		foreach($this->db->users as $user) {
			if($user->username === $username && $user->password === $password){
				$_SESSION['user']['authenticated']['id'] = $user->id;
				return true;
			}
		}
		return false;
	}

	public function isLoggedIn(){
		return isset($_SESSION['user']['authenticated']);
	}

	public function logout(){
		unset($_SESSION['user']['authenticated']);
		header('Location:'.location('',1));
	}

	private function writeJson($jsonPath, $jsonData){
		file_put_contents($jsonPath,json_encode($jsonData,JSON_PRETTY_PRINT));
	}

	public function getName($onlyFirst=false,$giveId=false){
		if($this->isLoggedIn()){
			$id = is_numeric($giveId) ? $giveId : $_SESSION['user']['authenticated']['id'];
			$name = $this->db->users[$id]->name;
			if($onlyFirst){
				$name = explode(' ', $name);
				$name = $name[0];
			}
			return $name;
		}
		return 'Sorry, not logged!';
	}

	public function getUsername($giveId=false){
		$id = is_numeric($giveId) ? $giveId : $_SESSION['user']['authenticated']['id'];
		return $this->db->users[$id]->username;
	}

	public function getIdByUsername($username){
		foreach($this->db->users as $user) {
			if($user->username === $username){
				return $user->id;
			}
		}
	}

	public function loadByUsername($username){
		foreach($this->db->users as $id => $user) {
			if($user->username === $username){
				return new User($id);
			}
		}
		return false;
	}

	public function removeAllChampion(){
		if(!empty($_SESSION['user']['authenticated']['id'])){
			global $db;
			foreach ($db->users as $dbUser){
				if($dbUser->id == $_SESSION['user']['authenticated']['id']){
					$dbUser->champions = [];
					file_put_contents('db_users.json',json_encode($db->users));
				}
			}
			return true;
		}else{
			return false;
		}
	}

	public function addChampion($id){
		if(!empty($_SESSION['user']['authenticated']['id'])){
			global $db;
			foreach ($db->users as $dbUser){
				if($dbUser->id == $_SESSION['user']['authenticated']['id']){
					if(!in_array($id,$dbUser->champions)){
						array_push($dbUser->champions,(int) $id);
						sort($dbUser->champions);
						$this->writeJson('db_users.json',$db->users);
						echo 'owned_champion:' . $id . "\n";
					}else{
						echo 'already_owned';
					}
				}
			}
		}else{
			echo 'not logged';
		}
	}

	public function removeChampion($id){
		if(!empty($_SESSION['user']['authenticated']['id'])){
			global $db;
			foreach ($db->users as $dbUser){
				if($dbUser->id == $_SESSION['user']['authenticated']['id']){
					if(in_array($id,$dbUser->champions)){
						unset($dbUser->champions[array_search((int)$id,$dbUser->champions)]);
						sort($dbUser->champions);
						$this->writeJson('db_users.json',$db->users);
						echo 'remove owned champion';
					}
					die;
				}
			}
		}else{
			echo 'not logged';
		}
	}

	public function addChampionSkin($id){
		if(!empty($_SESSION['user']['authenticated']['id'])){
			global $db;
			foreach ($db->users as $dbUser){
				if($dbUser->id == $_SESSION['user']['authenticated']['id']){
					if(!in_array($id,$dbUser->champions_skins)){
						array_push($dbUser->champions_skins,(int) $id);
						sort($dbUser->champions_skins);
						$this->writeJson('db_users.json',$db->users);
						echo 'owned_champions_skins:' . $id . "\n";
					}else{
						echo 'already_owned';
					}
				}
			}
		}else{
			echo 'not logged';
		}
	}

	public function removeChampionSkin($id){
		if(!empty($_SESSION['user']['authenticated']['id'])){
			global $db;
			foreach ($db->users as $dbUser){
				if($dbUser->id == $_SESSION['user']['authenticated']['id']){
					if(in_array($id,$dbUser->champions_skins)){
						unset($dbUser->champions_skins[array_search((int)$id,$dbUser->champions_skins)]);
						sort($dbUser->champions_skins);
						$this->writeJson('db_users.json',$db->users);
						echo 'remove owned champion skin';
					}
				}
			}
		}else{
			echo 'not logged';
		}
	}

	public function removeAllChampionSkin(){
		if(!empty($_SESSION['user']['authenticated']['id'])){
			global $db;
			foreach ($db->users as $dbUser){
				if($dbUser->id == $_SESSION['user']['authenticated']['id']){
					$dbUser->champions_skins = [];
					$this->writeJson('db_users.json',$db->users);
				}
			}
			return true;
		}else{
			return false;
		}
	}


	public function haveChampion($id){
		global $db;
		foreach ($db->users as $dbUser){
			if($dbUser->id == $this->getIdByUsername(rewrite(2))){
				if(in_array($id,$dbUser->champions)){
					return true;
				}
				return false;
			}
		}
	}

	public function haveChampionSkin($id){
		global $db;
		foreach ($db->users as $dbUser){
			if($dbUser->id == $this->getIdByUsername(rewrite(2))){
				if(in_array($id,$dbUser->champions_skins)){
					return true;
				}
				return false;
			}
		}
	}

	static public function getChampions($id){
		global $db;
		foreach ($db->users as $dbUser){
			if($dbUser->id == $id){
				return $dbUser->champions;
			}
		}
	}

	static public function getChampionsSkins($id){
		global $db;
		foreach ($db->users as $dbUser){
			if($dbUser->id == $id){
				return $dbUser->champions_skins;
			}
		}
	}

}
?>
