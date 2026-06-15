<?php
class LinkType
{
    private DoliDB  $_db;

    public int      $id;
    public string   $label;
    public string   $code;
    public string   $active;

    public function __construct(DoliDB $db)
    {
        $this->_db = $db;
    }


    public function fetch(int $id, $langs = null): void
    {
        if (is_null($id) || $id === 0 || $id < 0) {
            throw new Exception($langs ? $langs->trans("INCORRECT_ID_TO_FETCH_LINKTYPE", $id) : "Incorrect id (" . $id . ") to fetch LinkType");
        }
        $sql = "SELECT * FROM " . MAIN_DB_PREFIX . "c_dolilinks_link_type WHERE rowid=" . $id;
        $resql = $this->_db->query($sql);
        if ($resql === false) {
            throw new Exception(($langs ? $langs->trans("SQL_ERROR") . ": " : "SQL error: ") . $this->_db->lasterror());
        }
        $arr = $this->_db->fetch_array($resql);
        if (is_null($arr) || $arr === false) {
            throw new Exception($langs ? $langs->trans("FAIL_TO_FETCH_LINKTYPE") . ": " . $this->_db->lasterror() : "Fail to fetch LinkType as array: " . $this->_db->lasterror());
        }
        $this->id               = $arr['rowid'];
        $this->code             = $arr['code'];
        $this->active           = $arr['active'];
        $this->label            = $arr['label'];
    }




    /** @return LinkType[] */
    public function getAll($langs = null, bool $onlyActive = true): array
    {
        global $conf, $langs;
        $sql = "SELECT rowid as id FROM ".MAIN_DB_PREFIX."c_dolilinks_link_type";
        $sql .= $onlyActive ? " WHERE active=1" : "";
        $resql = $this->_db->query($sql);
        if ($resql === false) {
            throw new Exception(($langs ? $langs->trans("SQL_ERROR") . ": " : "SQL error: ") . $this->_db->lasterror());
        }
        $linkTypes = [];
        while($arr = $this->_db->fetch_array($resql)){
            $linkType = new LinkType($this->_db, $conf);
            $linkType->fetch($arr['id'], $langs);
            $linkTypes[$arr['id']] = $linkType; 
        }
        return $linkTypes;
    }




}
