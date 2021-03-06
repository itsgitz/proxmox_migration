<?php

namespace ProxmoxMigration\DB;

use ProxmoxMigration\DB\System;
use ProxmoxMigration\DB\DatabaseRepository as DB_REPO;


/**
 * Note: that this class is only using MySQL as database server
 */
class Database
{
    private $db;
    private $servername;
    private $username;
    private $password;

    const SUCCESS = 'success';
    const ERROR = 'error';

    public function __construct()
    {
        $this->db = getenv('DB_NAME');
        $this->servername = getenv('DB_SERVERNAME');
        $this->username = getenv('DB_USERNAME');
        $this->password = getenv('DB_PASSWORD');
    }

    /**
     * createConnection
     * 
     * @return object connection
     */
    public function getConnection()
    {
        // create new connection
        $conn = new \mysqli($this->servername, $this->username, $this->password, $this->db);

        return $conn;
    }

    /**
     * isConnectionError
     * 
     * check database connection status
     * 
     * @return bool true|false
     */
    public function isConnectionError()
    {
        if ($this->getConnection()->connect_error) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * getQuery
     * 
     * query or selecting data
     * 
     * @param string tablename
     * @param string where clauses
     * @param bool dev true|false
     * @return array data as assoc array
     */
    public function getQuery($table, $where = null, $dev = false)
    {
        if ($dev) {
            $sql = "SELECT * FROM $table WHERE $where";
        } else {
            $sql = "SELECT * FROM $table";
        }

        $result = $this->getConnection()->query($sql);
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * runImportData()
     * 
     * @param string filename
     * @param string tablename
     * @param string columns with csv format and enclosed by brackets. Example: (column1,column2,column3)
     * @return array|error array if success
     */
    public function runImportData($filename, $table, $columns)
    {
        // define a log file execution
        $log = System::LOGDIR . DIRECTORY_SEPARATOR . System::DB_LOGFILE;

        // enclosed character for display double quotes as a string
        $enclosedChar = '\\"';

        // define sql query
        $sqlQuery = "LOAD DATA LOCAL INFILE '$filename' 
            INTO TABLE $table 
            FIELDS TERMINATED BY ','
            ENCLOSED BY '$enclosedChar'
            LINES TERMINATED BY '\\n'
            $columns
        ";

        // define execution for sql query
        $sqlExecution = "mysql -u {$this->username} --password=\"{$this->password}\" {$this->db} -h {$this->servername} -e \"$sqlQuery\" > $log";

        // execute command
        exec($sqlExecution, $output, $return_var);

        // if return var is 1 or true, it means error occurred
        if ($return_var) {

            // return status error
            return [
                "status" => false,
                "message" => self::ERROR,
                "filename" => $filename,
                "table" => $table,
                "columns" => $columns
            ];

        } else {
            
            // return status success
            return [
                "status" => true,
                "message" => self::SUCCESS,
                "filename" => $filename,
                "table" => $table,
                "columns" => $columns
            ];
        }
    }

    /**
     * rollBackData()
     * 
     * Rollback data migration in development environment. Delete data that had been migrated before
     * 
     * @param string $hosting_id hosting_id
     * @return array $table_data
     */
    public function rollBackData($hosting_id)
    {
        /**
         * Define delete query for rollback data where id = given hosting_id
         */
        
        // tblhosting
        $tblhosting = "DELETE FROM " . DB_REPO::TBLHOSTING_TABLENAME . " WHERE id = $hosting_id";

        // tblcustomfieldsvalues
        $tblcustomfieldsvalues = "DELETE FROM " . DB_REPO::TBLCUSTOMFIELDSVALUES_TABLENAME . " WHERE relid = $hosting_id";

        // ProxmoxAddon_User
        $proxmoxAddonUser = "DELETE FROM ". DB_REPO::PROXMOX_ADDON_USER_TABLENAME . " WHERE hosting_id = $hosting_id";

        // ProxmoxAddon_VmIpAddress
        $proxmoxAddonVmIpAddress = "DELETE FROM " . DB_REPO::PROXMOX_ADDON_VMIPADDRESS_TABLENAME . " WHERE hosting_id = $hosting_id";

        // mg_proxmox_addon_ip
        $mgProxmoxAddonIp = "DELETE FROM " . DB_REPO::MG_PROXMOX_ADDON_IP_TABLENAME . " WHERE hosting_id = $hosting_id";

        // mod_proxmox_change_password_log
        $modProxmoxChangePasswordLog = "DELETE FROM " . DB_REPO::MOD_PROXMOX_CHANGE_PASSWORD_LOG_TABLENAME . " WHERE serviceid = $hosting_id";

        $result = [
            "hosting_id" => $hosting_id,
            DB_REPO::TBLHOSTING_TABLENAME => $this->getConnection()->query($tblhosting),
            DB_REPO::TBLCUSTOMFIELDSVALUES_TABLENAME => $this->getConnection()->query($tblcustomfieldsvalues),
            DB_REPO::PROXMOX_ADDON_USER_TABLENAME => $this->getConnection()->query($proxmoxAddonUser),
            DB_REPO::PROXMOX_ADDON_VMIPADDRESS_TABLENAME => $this->getConnection()->query($proxmoxAddonVmIpAddress),
            DB_REPO::MG_PROXMOX_ADDON_IP_TABLENAME => $this->getConnection()->query($mgProxmoxAddonIp),
            DB_REPO::MOD_PROXMOX_CHANGE_PASSWORD_LOG_TABLENAME => $this->getConnection()->query($modProxmoxChangePasswordLog),
        ];

        return $result;
    }
}