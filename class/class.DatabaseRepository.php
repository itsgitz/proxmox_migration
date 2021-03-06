<?php

namespace ProxmoxMigration\DB;

class DatabaseRepository
{
    /**
     * 
     * export/import definition
     * 
     * */ 
    const IMPORT = 'import';
    const EXPORT = 'export';
    const ROLLBACK = 'rollback';
    const STORAGE_DIR = './storage';
    const CSV_EXTENTIONS = '.csv';

    /**
     * System status message
     */
    const DB_CONNECTION_PROBLEM = "Database connection problem! \n";

    /* Table name and columns definition */

    /**
     * Existing tables on old Proxmox Addon Module
     */
    const PROXMOXVPS_USERS_TABLENAME = 'proxmoxVPS_Users'; // => ProxmoxAddon_User
    const PROXMOXVPS_USERS_CSV_FILES = self::STORAGE_DIR . DIRECTORY_SEPARATOR . self::PROXMOXVPS_USERS_TABLENAME . self::CSV_EXTENTIONS;

    const PROXMOXVPS_IP_TABLENAME = 'proxmoxVPS_IP'; // => ProxmoxAddon_VmIpAddress
    const PROXMOXVPS_IP_CSV_FILES = self::STORAGE_DIR . DIRECTORY_SEPARATOR . self::PROXMOXVPS_IP_TABLENAME . self::CSV_EXTENTIONS;

    /**
     * tblhosting (only for production to development migration)
     */
    const TBLHOSTING_TABLENAME = 'tblhosting';
    const TBLHOSTING_COLUMNS = '(id, userid, orderid, packageid, server, regdate, domain, paymentmethod, firstpaymentamount, amount, billingcycle, nextduedate, nextinvoicedate, termination_date, completed_date, domainstatus, username, password, notes, subscriptionid, promoid, suspendreason, overideautosuspend, overidesuspenduntil, dedicatedip, assignedips, ns1, ns2, diskusage, disklimit, bwusage, bwlimit, lastupdate, created_at, updated_at)';
    const TBLHOSTING_CSV_FILES = self::STORAGE_DIR . DIRECTORY_SEPARATOR . self::TBLHOSTING_TABLENAME . self::CSV_EXTENTIONS;

    /**
     * tblcustomfieldsvalues (only for production to development migration)
     */
    const TBLCUSTOMFIELDSVALUES_TABLENAME = 'tblcustomfieldsvalues';
    const TBLCUSTOMFIELDSVALUES_COLUMNS = '(id,fieldid,relid,value,created_at,updated_at)';
    const TBLCUSTOMFIELDSVALUES_CSV_FILES = self::STORAGE_DIR . DIRECTORY_SEPARATOR . self::TBLCUSTOMFIELDSVALUES_TABLENAME . self::CSV_EXTENTIONS;

    /**
     * mod_proxmox_change_password_log (only for production to development migration)
     */
    const MOD_PROXMOX_CHANGE_PASSWORD_LOG_TABLENAME = 'mod_proxmox_change_password_log';
    const MOD_PROXMOX_CHANGE_PASSWORD_LOG_COLUMNS = '(id,serviceid,password,update_at)';
    const MOD_PROXMOX_CHANGE_PASSWORD_LOG_CSV_FILES = self::STORAGE_DIR . DIRECTORY_SEPARATOR . self::MOD_PROXMOX_CHANGE_PASSWORD_LOG_TABLENAME . self::CSV_EXTENTIONS;

    /**
     * ProxmoxAddon_User (new table) of proxmoxVPS_Users
     */
    const PROXMOX_ADDON_USER_TABLENAME = 'ProxmoxAddon_User';
    const PROXMOX_ADDON_USER_COLUMNS = '(id,user_id,hosting_id,username,password,realm)';
    const PROXMOX_ADDON_USER_CSV_FILES = self::STORAGE_DIR . DIRECTORY_SEPARATOR . self::PROXMOX_ADDON_USER_TABLENAME . self::CSV_EXTENTIONS;

    /**
     * ProxmoxAddon_VmIpAddress
     */
    const PROXMOX_ADDON_VMIPADDRESS_TABLENAME = 'ProxmoxAddon_VmIpAddress';
    const PROXMOX_ADDON_VMIPADDRESS_COLUMNS = '(id,hosting_id,server_id,ip,mac_address,subnet_mask,gateway,cidr,trunks,tag,net)';
    const PROXMOX_ADDON_VMIPADDRESS_CSV_FILES = self::STORAGE_DIR . DIRECTORY_SEPARATOR . self::PROXMOX_ADDON_VMIPADDRESS_TABLENAME . self::CSV_EXTENTIONS;

    /**
     * mg_proxmox_addon_ip
     */
    const MG_PROXMOX_ADDON_IP_TABLENAME = 'mg_proxmox_addon_ip';
    const MG_PROXMOX_ADDON_IP_COLUMNS = '(id,ip,type,mac_address,subnet_mask,gateway,cidr,sid,visualization,last_check,private,hosting_id,trunks,tag,node)';
    const MG_PROXMOX_ADDON_IP_CSV_FILES = self::STORAGE_DIR . DIRECTORY_SEPARATOR . self::MG_PROXMOX_ADDON_IP_TABLENAME . self::CSV_EXTENTIONS;

    /**
     * mg_proxmox_vmranges
     */
    const MG_PROXMOX_VMRANGES_TABLENAME = 'mg_proxmox_vmranges';
    const MG_PROXMOX_VMRANGES_COLUMNS = '(server_id,vmid_from,vmid_to)';
    const MG_PROXMOX_VMRANGES_CSV_FILES = self::STORAGE_DIR . DIRECTORY_SEPARATOR . self::MG_PROXMOX_VMRANGES_TABLENAME . self::CSV_EXTENTIONS;

    /* Users table, only for testing on local database server */
    const USERS_TABLENAME = 'users';
    const USERS_COLUMNS = '(id,created_at,updated_at,deleted_at,username,name,password,role,job_title)';
    const USERS_CSV_FILES = self::STORAGE_DIR . DIRECTORY_SEPARATOR . self::USERS_TABLENAME . self::CSV_EXTENTIONS;

    /**
     * showListTables
     * 
     * print all tables name information that will be migrated
     * 
     * @param bool dev, true|false
     */
    public function showListTables($dev = false)
    {
        // Date
        $date = date('D, d F Y');

        // Print all tables that want to migrate
        echo "\n** $date \n";
        echo "** All tables that will migrate along with csv filename that should be produced after exporting data on table: \n\n";

        // If dev mode
        if ($dev) {
            echo "- " . self::TBLHOSTING_TABLENAME . ", '" . self::TBLHOSTING_CSV_FILES . "' \n";
            echo "- " . self::TBLCUSTOMFIELDSVALUES_TABLENAME . ", '" . self::TBLCUSTOMFIELDSVALUES_CSV_FILES . "' \n";
            echo "- " . self::MOD_PROXMOX_CHANGE_PASSWORD_LOG_TABLENAME . ", '" . self::MOD_PROXMOX_CHANGE_PASSWORD_LOG_CSV_FILES . "' \n";
        }

        echo "- " . self::PROXMOXVPS_USERS_TABLENAME . " => " . self::PROXMOX_ADDON_USER_TABLENAME . ", '" . self::PROXMOX_ADDON_USER_CSV_FILES . "' \n";
        echo "- " . self::PROXMOXVPS_IP_TABLENAME . " => " . self::PROXMOX_ADDON_VMIPADDRESS_TABLENAME . ", '" . self::PROXMOX_ADDON_VMIPADDRESS_CSV_FILES . "' \n";
        echo "- " . self::MG_PROXMOX_ADDON_IP_TABLENAME .  ", '" . self::MG_PROXMOX_ADDON_IP_CSV_FILES . "' \n";
        echo "- " . self::MG_PROXMOX_VMRANGES_TABLENAME .  ", '" . self::MG_PROXMOX_VMRANGES_CSV_FILES . "' \n";
        echo "\n";
    }

    /**
     * getHostingId()
     * 
     * Get hosting_id from user input (prompt)
     * 
     * @return int|string $hosting_id
     */
    public function getHostingId()
    {
        $hosting_id = readline("Please enter Hosting ID: ");

        return $hosting_id;
    }

    /**
     * generateWhereClauses
     * 
     * Generate where clauses for sql query with given whmcs hosting_id / service_id
     * 
     * @param int|string $hosting_id
     * @return array where clauses for each table
     */
    public function generateWhereClauses($hosting_id)
    {
        return [
            self::TBLHOSTING_TABLENAME => "id = $hosting_id",
            self::TBLCUSTOMFIELDSVALUES_TABLENAME => "relid = $hosting_id",
            self::PROXMOXVPS_USERS_TABLENAME => "hosting_id = $hosting_id",
            self::PROXMOXVPS_IP_TABLENAME => "hid = $hosting_id",
            self::MG_PROXMOX_ADDON_IP_TABLENAME => "hosting_id = $hosting_id",
            self::MOD_PROXMOX_CHANGE_PASSWORD_LOG_TABLENAME => "serviceid = $hosting_id",
        ];
    }
}