<?php
class UserLoginFailedException extends Exception{}

class wbUser extends Object{
    public static $userSessions = array();

    public static function init(){
        $user_id = wbSession::getVar('user_id');
        if ($user_id === null){
            self::setSession(WB_USER_UNREGISTERED_ID, WB_USER_UNREGISTERED_NAME);
            // Generate a random number, used for
            // some authentication
            srand((double) microtime() * 1000000);
            wbSession::setVar('rand', array(time() . '-' . rand()));
        }
    }

    public static function isLoggedIn()
    {
        $currUid = wbSession::getVar('user_id');
        if ($currUid == WB_USER_UNREGISTERED_ID || $currUid === null) return false;

        return true;
    }

    public static function logIn($userName, $password, $rememberMe = 0)
    {
        if (self::isLoggedIn()) return true;

        if (empty($userName)) throw new UserLoginFailedException('username tidak boleh kosong');
        if (empty($password)) throw new UserLoginFailedException('password tidak boleh kosong');

        $dbconn = wbDB::getConn();
        //$table = wbConfig::get('DB.prefix').'_user';
        //$query = "SELECT user_id, user_name, user_password, user_email, user_realname, user_status FROM $table WHERE user_name = ?";
        $table = 'sikp.p_app_user';
        $query = "SELECT p_app_user_id as user_id,app_user_name as user_name,user_pwd as user_password,email_address as user_email,full_name as user_realname,p_user_status_id as user_status FROM $table WHERE app_user_name = '".trim($userName)."'";
		$result =& $dbconn->Execute($query);
        //$result =& $dbconn->Execute($query,array($userName));
        if (!$result) return;
        if ($result->EOF) {
            $result->Close();
            throw new UserLoginFailedException('User '.$userName.' belum terdaftar');
        }
		$items_user = $result->fields;
		
		if($items_user['is_employee'] == 'Y'){
			throw new UserLoginFailedException('User '.$userName.' belum terdaftar');
		}
        list($user_id, $user_name, $user_password, $user_email, $user_realname, $user_status) = $result->fields;
        $result->Close();

        if ($user_status != WB_STATUS_ACTIVE) throw new UserLoginFailedException('Account user anda tidak aktif. Silahkan hubungi administrator untuk info lebih lanjut');

        // Confirm that passwords match
        $md5pass = md5($password);
        if (strcmp($md5pass, trim($user_password)) != 0) throw new UserLoginFailedException('Password anda tidak sama. Mohon periksa kembali');
        
        $query = "select * from core_user_role where user_id = ?";

        $result =& $dbconn->Execute($query,array($user_id));
        $core_user_role = $result->fields;
        if(empty($core_user_role['role_id'])){
        
            $query = "INSERT INTO public.core_user_role (user_id, role_id, main_role) VALUES (?, '2', '1')";
    
            $result =& $dbconn->Execute($query,array($user_id));
        }
        
        $roles = self::getUserRoles($user_id);

        self::setSession($user_id, $user_name, $user_email, $user_realname, $roles);

        return $user_id;
    }
	
	public static function logInCard($cardNumber, $npwpd, $rememberMe = 0)
    {
        if (self::isLoggedIn()) return true;

        if (empty($cardNumber)) throw new UserLoginFailedException('nomor kartu tidak boleh kosong');
        if (empty($npwpd)) throw new UserLoginFailedException('npwpd tidak boleh kosong');

        $dbconn = wbDB::getConn();
        //$table = wbConfig::get('DB.prefix').'_user';
        //$query = "SELECT user_id, user_name, user_password, user_email, user_realname, user_status FROM $table WHERE user_name = ?";
        $query = "SELECT t_cust_account.t_cust_account_id,p_app_user_id 
					from t_cust_acc_card
						left join t_cust_account on t_cust_acc_card.t_cust_account_id = t_cust_account.t_cust_account_id
						left join t_customer_user on t_cust_account.t_customer_id = t_customer_user.t_customer_id
					where 
						card_number = ? and t_cust_account.npwd = ?";
		$result =& $dbconn->Execute($query,array($cardNumber, $npwpd));
        if (!$result) return;
        if ($result->EOF) {
            $result->Close();
            throw new UserLoginFailedException('User '.$userName.' belum terdaftar');
        }
		$table = 'sikp.p_app_user';
        $query = "SELECT p_app_user_id as user_id,app_user_name as user_name,user_pwd as user_password,email_address as user_email,full_name as user_realname,p_user_status_id as user_status FROM $table WHERE p_app_user_id = ".$result->fields['p_app_user_id'];
		$result =& $dbconn->Execute($query);
		
		
		if($items_user['is_employee'] == 'Y'){
			throw new UserLoginFailedException('User '.$userName.' belum terdaftar');
		}
        list($user_id, $user_name, $user_password, $user_email, $user_realname, $user_status) = $result->fields;
        $result->Close();

        if ($user_status != WB_STATUS_ACTIVE) throw new UserLoginFailedException('Account user anda tidak aktif. Silahkan hubungi administrator untuk info lebih lanjut');
        $query = "select * from core_user_role where user_id = ?";

        $result =& $dbconn->Execute($query,array($user_id));
        $core_user_role = $result->fields;
        if(empty($core_user_role['role_id'])){
        
            $query = "INSERT INTO public.core_user_role (user_id, role_id, main_role) VALUES (?, '2', '1')";
    
            $result =& $dbconn->Execute($query,array($user_id));
        }
        
        $roles = self::getUserRoles($user_id);

        self::setSession($user_id, $user_name, $user_email, $user_realname, $roles);

        return $user_id;
    }
	
    public static function logOut()
    {
        if (!self::isLoggedIn()) return true;

        self::delSession();
        // clear all previously set authkeys, then generate a new value
        srand((double) microtime() * 1000000);
        wbSession::setVar('rand', array(time() . '-' . rand()));


        return true;
    }

    public static function setSession($user_id, $user_name, $user_email = '', $user_realname = '', $roles = array()){
        wbSession::setVar('user_id', $user_id);
        wbSession::setVar('user_name', $user_name);
        wbSession::setVar('user_email', $user_email);
        wbSession::setVar('user_realname', $user_realname);
        wbSession::setVar('roles', serialize($roles));

        return true;
    }

    public static function delSession(){
        wbSession::setVar('user_id', WB_USER_UNREGISTERED_ID);
        wbSession::setVar('user_name', WB_USER_UNREGISTERED_NAME);
        wbSession::setVar('user_email', '');
        wbSession::setVar('user_realname', '');
        wbSession::setVar('roles', serialize(array()));
        return true;
    }

    public static function &getSession(){
        $info = array();
        $info['user_id'] = wbSession::getVar('user_id');
        $info['user_name'] = wbSession::getVar('user_name');
        $info['user_email'] = wbSession::getVar('user_email');
        $info['user_realname'] = wbSession::getVar('user_realname');
        $info['roles'] = unserialize(wbSession::getVar('roles'));

        return $info;
    }

    public static function &getUserRoles($uid){
        $dbconn = wbDB::getConn();

        $prefix = wbConfig::get('DB.prefix');
        $query = "SELECT a.role_id, b.role_name "
                ."FROM ". $prefix ."_user_role as a, ". $prefix ."_role as b "
                ."WHERE a.role_id = b.role_id AND a.user_id = ?";

        $result =& $dbconn->Execute($query, array($uid));
        if (!$result) return;

        $roles = array();
        while (!$result->EOF) {
            list($role_id, $role_name) = $result->fields;
            $roles[] = array('role_id' => $role_id, 'role_name' => $role_name);
            $result->MoveNext();
        }
        $result->Close();
        return $roles;
    }


}