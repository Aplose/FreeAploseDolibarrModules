<?php
class SocieteLink
{
    private DoliDB  $_db;
    // private User    $_user;

    public int      $id;
    public int      $fk_parent;
    public int      $fk_child;
    public int      $entity;
    public int      $fk_link_type;
    public string   $date_creation;
    public string   $tms;
    public int      $fk_user_creat;


    public function __construct(DoliDB $db)
    {
        $this->_db = $db;
    }


    /** @return int Retourne l'id de la ligne insérée */
    public function create(int $fkParent, int $fkChild, User $user, int $fkLinkType = null, $langs = null): int
    {
        if ($fkParent <= 0) {
            throw new Exception($langs ? $langs->trans("PARENT_ID_MUST_BE_GREATER_THAN_0") : "Parent id must be greater than 0");
        }
        if ($fkChild <= 0) {
            throw new Exception($langs ? $langs->trans("CHILD_ID_MUST_BE_GREATER_THAN_0") : "Child id must be greater than 0");
        }

        if ($this->linkExists($fkParent, $fkChild, $fkLinkType)) {
            throw new Exception($langs ? $langs->trans("LINK_ALREADY_EXISTS") : "Link already exists");
        }

        if (!is_null($fkLinkType) && $fkLinkType > 0) {
            // TODO Vérifier que le type de lien existe
            $this->fk_link_type = $fkLinkType;
        }

        $sql = "INSERT INTO " . MAIN_DB_PREFIX . "dolilinks_societe_link (";
        $sql .= "fk_parent, fk_child, entity, fk_link_type, date_creation, tms, fk_user_creat";
        $sql .= ") VALUES (";
        $sql .= $fkParent . ", ";
        $sql .= $fkChild . ", ";
        $sql .= $user->entity . ", ";
        $sql .= (is_null($fkLinkType) ? "NULL" : $fkLinkType) . ", ";
        $sql .= "'" . date('Y-m-d H:i:s') . "', ";
        $sql .= "'" . date('Y-m-d H:i:s') . "', ";
        $sql .= $user->id;
        $sql .= ")";

        $resql = $this->_db->query($sql);
        if ($resql) {
            $this->id = (int)$this->_db->last_insert_id(MAIN_DB_PREFIX . "dolilinks_societe_link");
            return $this->id;
        } else {
            throw new Exception(($langs ? $langs->trans("SQL_ERROR") . ": " : "Error ") . $this->_db->lasterror());
        }
    }




    public function fetch(int $id, $langs = null): bool
    {
        $sql = "SELECT rowid, fk_parent, fk_child, entity, fk_link_type, date_creation, tms, fk_user_creat";
        $sql .= " FROM " . MAIN_DB_PREFIX . "dolilinks_societe_link";
        $sql .= " WHERE rowid = " . intval($id);

        $resql = $this->_db->query($sql);
        if ($resql) {
            if ($this->_db->num_rows($resql) > 0) {
                $obj = $this->_db->fetch_object($resql);
                $this->id = (int)$obj->rowid;
                $this->fk_parent = (int)$obj->fk_parent;
                $this->fk_child = (int)$obj->fk_child;
                $this->entity = (int)$obj->entity;
                $this->fk_link_type = (int)$obj->fk_link_type;
                $this->date_creation = $obj->date_creation;
                $this->tms = $obj->tms;
                $this->fk_user_creat = (int)$obj->fk_user_creat;
                return true;
            } else {
                return false;
            }
        } else {
            throw new Exception(($langs ? $langs->trans("SQL_ERROR") . ": " : "Error ") . $this->_db->lasterror());
        }
    }


    /** @return SocieteLink[] */
    public function getByLinkTypeId(int $linkTypeId, $langs = null): array
    {
        $sql = "SELECT * FROM ".MAIN_DB_PREFIX."dolilinks_societe_link ";
        $sql .= "WHERE fk_link_type=". intval($linkTypeId);

        $resql = $this->_db->query($sql);
        if($resql === false){
            throw new Exception(($langs ? $langs->trans("SQL_ERROR") . ": " : "SQL Error: ") . $this->_db->lasterror());
        }
        if($resql->num_rows === 0){
            return [];
        }
        $societeLinks = [];
        while ($arr = $this->_db->fetch_array($resql)) {
            $societeLink = new SocieteLink($this->_db);
            $societeLink->fetch($arr['rowid']);
            $societeLinks[] = $societeLink;
        }
        return $societeLinks;
    }


    /** @return Societe[] */
    public function getParents(int $childId, int $max = -1, $langs = null): array
    {
        $sql = "SELECT rowid, fk_parent, fk_child, entity, fk_link_type, date_creation, tms, fk_user_creat";
        $sql .= " FROM " . MAIN_DB_PREFIX . "dolilinks_societe_link";
        $sql .= " WHERE fk_child = " . intval($childId);
        if ($max > 0) {
            $sql .= " limit " . $max;
        }

        $resql = $this->_db->query($sql);
        if ($resql === false) {
            throw new Exception(($langs ? $langs->trans("SQL_ERROR") . ": " : "Error ") . $this->_db->lasterror());
        }
        $societies = array();
        while ($arr = $this->_db->fetch_array($resql)) {
            $societe = new Societe($this->_db);
            $result = $societe->fetch($arr['fk_parent']);
            if ($result < 0) {
                throw new Exception(($langs ? $langs->trans("SQL_ERROR") . ": " : "Error ") . $this->_db->lasterror());
            }
            if ($result === 0) {
                throw new Exception($langs ? $langs->trans("PARENT_COMPANY_NOT_FOUND", $arr['fk_parent']) : "Parent company with id " . $arr['fk_parent'] . " not found");
            }
            $societies[] = $societe;
        }
        return count($societies) > 0 ? $societies : [];
    }



    public function getParentsCount(int $childId, $langs = null): int
    {
        return count($this->getParents($childId, -1, $langs));
    }



    /** @return Societe[] */
    public function getChilds(int $parentId, int $max = -1, $langs = null): array
    {
        $sql = "SELECT rowid, fk_parent, fk_child, entity, fk_link_type, date_creation, tms, fk_user_creat";
        $sql .= " FROM " . MAIN_DB_PREFIX . "dolilinks_societe_link";
        $sql .= " WHERE fk_parent = " . intval($parentId);
        if ($max > 0) {
            $sql .= " limit " . $max;
        }

        $resql = $this->_db->query($sql);
        if ($resql === false) {
            throw new Exception(($langs ? $langs->trans("SQL_ERROR") . ": " : "Error ") . $this->_db->lasterror());
        }
        $societies = array();
        while ($arr = $this->_db->fetch_array($resql)) {
            $societe = new Societe($this->_db);
            $result = $societe->fetch($arr['fk_child']);
            if ($result < 0) {
                throw new Exception(($langs ? $langs->trans("SQL_ERROR") . ": " : "Error ") . $this->_db->lasterror());
            }
            if ($result === 0) {
                throw new Exception($langs ? $langs->trans("CHILD_COMPANY_NOT_FOUND", $arr['fk_child']) : "Child company with id " . $arr['fk_child'] . " not found");
            }
            $societies[] = $societe;
        }
        return count($societies) > 0 ? $societies : [];
    }


    public function getChildsCount(int $parentId, $langs = null): int
    {
        return count($this->getChilds($parentId, -1, $langs));
    }


    /** @return string[] les id des societe parent*/
    public function getParentIds(int $childId, $langs = null): array
    {
        $ids = array();
        foreach ($this->getParents($childId, -1, $langs) as $parent) {
            $ids[] = $parent->id;
        }
        return $ids;
    }



    /** @return string[] les id des societe enfant*/
    public function getChildIds(int $parentId, $langs = null): array
    {
        $ids = array();
        foreach ($this->getChilds($parentId, -1, $langs) as $child) {
            $ids[] = $child->id;
        }
        return $ids;
    }





    public function deleteParent(int $currentSocieteId, int $parentId, $langs = null): bool
    {
        $sql = "DELETE FROM " . MAIN_DB_PREFIX . "dolilinks_societe_link WHERE fk_child=" . $currentSocieteId . " AND fk_parent=" . intval($parentId);
        $resql = $this->_db->query($sql);
        if ($resql) {
            return true;
        } else {
            throw new Exception(($langs ? $langs->trans("SQL_ERROR") . ": " : "Error ") . $this->_db->lasterror());
        }
    }



    public function deleteChild(int $currentSocieteId, int $childId, $langs = null): bool
    {
        $sql = "DELETE FROM " . MAIN_DB_PREFIX . "dolilinks_societe_link WHERE fk_parent=" . $currentSocieteId . " AND fk_child=" . intval($childId);
        $resql = $this->_db->query($sql);
        if ($resql) {
            return true;
        } else {
            throw new Exception(($langs ? $langs->trans("SQL_ERROR") . ": " : "Error ") . $this->_db->lasterror());
        }
    }



    public function linkExists(int $fkParent, int $fkChild, int $fkLinkType = null, $langs = null): bool
    {
        $sql = "SELECT rowid";
        $sql .= " FROM " . MAIN_DB_PREFIX . "dolilinks_societe_link";
        $sql .= " WHERE fk_parent = " . intval($fkParent);
        $sql .= " AND fk_child = " . intval($fkChild);
        // if ($fkLinkType > 0) {
        //     $sql .= " AND fk_link_type = " . intval($fkLinkType);
        // }

        $resql = $this->_db->query($sql);
        if ($resql) {
            if ($this->_db->num_rows($resql) > 0) {
                return true;
            } else {
                return false;
            }
        } else {
            throw new Exception(($langs ? $langs->trans("SQL_ERROR") . ": " : "SQL Error: ") . $this->_db->lasterror());
        }
    }



    public function getLinkTypeId(int $fkParent, int $fkChild, $langs = null): ?int
    {
        $sql = "SELECT fk_link_type";
        $sql .= " FROM " . MAIN_DB_PREFIX . "dolilinks_societe_link";
        $sql .= " WHERE fk_parent = " . intval($fkParent);
        $sql .= " AND fk_child = " . intval($fkChild);

        $resql = $this->_db->query($sql);
        if ($resql === false) {
            throw new Exception(($langs ? $langs->trans("SQL_ERROR") . ": " : "SQL Error: ") . $this->_db->lasterror());
        }

        $arr = $this->_db->fetch_array($resql);
        // if (!isset($arr['fk_link_type'])) {
        //     throw new Exception("Fail to fecth SocieteLink::fk_link_type on parent id is " . $fkParent . " and child id is " . $fkChild . ". Error: " . $this->_db->lasterror());
        // }

        return  $arr['fk_link_type'];
    }


    // public function unsetFkLinkType(): void
    // {
    //     $this->fk_link_type = -1;

    //     $sql = "UPDATE ". MAIN_DB_PREFIX ."dolilinks_societe_link ";
    //     $sql .= "SET fk_link_type=-1";

    //     if($this->_db->query($sql) === false){
    //         throw new Exception("Fail to unset fk_link_type. Error: ".$this->_db->lasterror());
    //     }
    // }
}
