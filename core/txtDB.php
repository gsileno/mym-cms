<?php 

// to be done: deleting mantaining position 
//             insert with sort

// standard permission for database dir (unix form + octale, ex.0777, 0755)
define('DB_CHMOD', '0755');

// standard separator string 
define('FIELD_SEPARATOR', '||');

// string left at the place of deleted elements (if position should be mantained)
define('DELETED', 'deleted');

// file extension for tables (ex: data, csv, ecc) 
define('TABLE_EXTENSION', 'data');

/**
 * Txtdb
 *
 * @package	Txtdb
 * @category	Database
 * @author	Giovanni Sileno
 * @link	http://www.mexpro.it
 */
class Txtdb {
  var $dbpath = "",
    $dbname = "",
    $tableext = TABLE_EXTENSION,
    $tablelist = array(),
    $fieldseparator = FIELD_SEPARATOR;
   
  var $where = "",
    $from = null,
    $order = array(),
    $limit = 0,
    $offset = 0,
    $cachequery = array();

/**
 * Txtdb constructor. Directory setup.
 *
 * @access	public
 * @param	string	db name
 * @param	string	base path to the db
 * @param	string	directory permission (linux/unix chmod in a octal mode) 
 * @return	void	
 */   
  public function __construct($params = null, $dbbasepath = null, $tableext = TABLE_EXTENSION, $fieldseparator = FIELD_SEPARATOR, $dbchmod = DB_CHMOD) {
     
    // first parameter can be an array with all parameters
    if (is_array($params)) {
      if (array_key_exists('name', $params))
        $table = $params['name'];
      if (array_key_exists('path', $params))
        $path = $params['path'];
      if (array_key_exists('dbchmod', $params))
        $dbchmod = $params['dbchmod'];
      if (array_key_exists('fieldseparator', $params))
        $fieldseparator = $params['fieldseparator'];
      if (array_key_exists('tableext', $params))
        $tableext = $params['tableext'];
    } else 
      $dbname = $params;
      
    if ($dbname === null || $dbname === "")
      show_error("You must define a name for the database.");
    $this->dbname = $dbname;
        
    if (!is_dir($dbbasepath)) 
      show_error("The directory <em>$dbbasepath</em> [".realpath($dbbasepath)."] for the database <strong>$dbname</strong> is not valid.");

    $this->dbpath = realpath($dbbasepath)."/".$dbname;

    if (file_exists($this->dbpath)) {
      if (!is_dir($this->dbpath))
      show_error("[".$this->dbpath."] > This is not a directory.");
    } else {
      if (!mkdir($this->dbpath, $dbchmod)) 
        show_error("[".$this->dbpath."] > Sorry, I cannot create this directory.");
    }    
   
    if ($tableext === null || $tableext === "")
      show_error("You must give an extension for table files.");
    
    if ($fieldseparator === null || $fieldseparator === "")
      show_error("You must give a correct field separator character for table files.");
    
    $this->tableext = strtolower($tableext);
    $this->fieldseparator = $fieldseparator;
  }
  
  function refresh_list_tables() {
    $tables = $times = $tablelist = array();
    $dhandle = opendir($this->dbpath);
    while ($file = readdir($dhandle)) {
      if (!(($file == ".") || ($file == "..") || ($file === "") || ($file[0] == "."))) {
        $path_parts = pathinfo($file);
	    if ($path_parts['extension'] === $this->tableext) {
	      $name = preg_replace("/(\w+).(\w+)$/", "$1", $path_parts['basename']);
          
          if (!array_key_existes($name, $this->tables))
            $this->tables[$name] = NULL;
            
	      $times[$name] = filemtime($this->dbpath."/".$file);
	      $tablelist[] = $name;
	    }
      }
    }    
    return array($tablelist, $times);
  }

  function list_timetables() {
    list($tablelist, $times) = $this->refresh_list_tables();
    asort($times); reset($times);
    return $times; 
  }
  
  function list_tables() {
    list($tablelist, $times) = $this->refresh_list_tables();
    sort($tablelist);
    reset($tablelist);
    return $tablelist; 
  }

  function table_exists($name = false) {
    if (!$name) return false;
    $this->refresh_list_tables();
    return in_array($name, $this->list_tables());
  }

  function create_table($name = false, $fields = array(), $notoverwrite = false) {
    if (!$name || count($fields) === 0) return false;
    if ($this->table_exists($name) && $notoverwrite) return false;

    $params = array("name" => $name,
		    "path" => $this->dbpath,
		    "containsfields" => true,
		    "fields" => $fields,
		    "separator" => $this->fieldseparator,
		    "tableext" => $this->tableext);

    $table = new Txttable($params);
    $table->createTable(!$notoverwrite);

    $this->refresh_list_tables();
    
    return true;
  }

  function tablefilename($name) {
    return $this->dbpath."/".$name.".".$this->tableext;
  }

  function drop_table($name = false) {
    if (!$name) return false;
    if (!$this->table_exists($name)) return false;
    if (!unlink($this->tablefilename($name))) return false;
    $this->refresh_list_tables();
    return true;
  }

  function rename_table($name1 = false, $name2 = false) {
    if (!$name1 || !$name2) return false;
    if (!$this->table_exists($name1) || $this->table_exists($name2)) return false;
    if (!rename($this->tablefilename($name1), $this->tablefilename($name2))) return false;
    $this->refresh_list_tables();
    return true;
  }

  function where($where) {
    $this->where = $where;
    return $this;
  }

  function order_by($field, $order = "asc") {
    $this->order = array($field, $order);
    return $this;
  }

  function limit($limit = 0, $offset = 0) {
    $this->limit = $limit;
    $this->offset = $offset;
    return $this;
  }

  function from($table_name) {
    $this->from = $table_name;
    return $this;
  }

  function open_table($table_name) {
    if (!$table_name) return false;
    if (!$this->table_exists($table_name)) return false;    
    if ($this->tables[$table_name] === null) {
      $params = array("name" => $table_name,
		      "path" => $this->dbpath,
		      "containsfields" => true,
		      "separator" => $this->fieldseparator,
		      "tableext" => $this->tableext);
    
      $this->tables[$table_name] = new Txttable($params);
      $this->tables[$table_name]->openTable();
    }
    return true;
  }

  function count_all($table_name = false) {
    if (!$table_name) $table_name = $this->from;
    if (!$this->open_table($table_name)) return false;
    return $this->tables[$table_name]->ndata;
  }

  function insert($table_name = false, $array) {
    if (!$table_name) return false;  
    if (!$this->open_table($table_name)) return false;
    return $this->tables[$table_name]->addElement($array);
  }

  function update($table_name = false, $array) {
    if (!$table_name) return false;
    if (!$this->open_table($table_name)) return false;
    if ($this->where === "") return false;
    $list_id = $this->tables[$table_name]->select($this->where);
    $result = 0;
    foreach ($list_id as $id) {
      if ($this->tables[$table_name]->modifyElement($id, $array))
	$result++;
    }
    return $result;
  }

  function get($table_name = false) {
    if (!$table_name) $table_name = $this->from;
    if (!$this->open_table($table_name)) return false;
    
    $result = array();
    $list_id = $this->tables[$table_name]->select($this->where);
    foreach ($list_id as $id) {
      $result[] = $this->tables[$table_name]->readElement($id);
    }
    return $result;
  }
}

/**
 * Txttable
 *
 * @package	Txttable
 * @category	Database
 * @author	Giovanni Sileno
 * @link	http://www.mexpro.it
 */
class Txttable {

  var $path = "",
      $table = "",
      $filename = "",
      $open = false,
      $hasfields = true,
      $fields = array(),
      $data = array(),
      $ndata = 0,
      $separator = FIELD_SEPARATOR,
      $concrete_delete = true;
      
/**
 * Txttable constructor. Table setup.
 *
 * @access	public
 * @param	string	table name
 * @param	string	path to the file  
 * @param	bool	true if the first line contains the names of the fields (fields) 
 * @param	array	array containing the fields (necessary with NOFIELDS)  
 * @param	string	fields' inline separator 
 * @param	bool	true if elements will be deleted with all the row, false if at their place there will be a "\n"  
 * @return	void	
 */
  public function __construct($params = null, $path = null, $containsfields = true, $fields = array(), $separator = FIELD_SEPARATOR, $concrete_delete = true, $tableext = TABLE_EXTENSION) {
    
    // first parameter can be an array with all parameters
    if (is_array($params)) {
      if (array_key_exists('name', $params))
        $table = $params['name'];
      if (array_key_exists('path', $params)) 
        $path = $params['path'];
      if (array_key_exists('containsfields', $params))
        $containsfields = $params['containsfields'];
      if (array_key_exists('fields', $params))
        $fields = $params['fields'];
      if (array_key_exists('separator', $params))
        $separator = $params['separator'];
      if (array_key_exists('concrete_delete', $params))
        $concrete_delete = $params['concrete_delete'];
      if (array_key_exists('tableext', $params))
        $tableext = $params['tableext'];
	
    } else 
      $table = $params;
        
    if ($table === null)
      show_error("You must define a name for the table.");
    
    if (!is_dir($path)) 
      show_error("The directory [$path] is not valid.");
    
    $this->path = $path; 
    $this->table = $table; 
    $this->filename = realpath($this->path)."/".$this->table.(($tableext != "")?".".$tableext:""); 
    $this->containsfields = $containsfields; 
    $this->separator = $separator; 
    $this->concrete_delete = true;
    
    if ($containsfields === false)
      if (count($fields) > 0) {
        // for ($i=0; $i<count($fields); $i++)
        //   $fields[$i] = strtolower($fields[$i]);	
        $this->fields = $fields;
      } else {
        show_error("You must define some fields (names of the table fields).");
      }       

    if (count($fields) > 0)
      $this->fields = $fields;
  }      
    
/**
 * Create a new plain text file containing the table. 
 *
 * @access	public
 * @param	boolean	true to overwrite an existing table with the same name and path
 * @return	true if the table is created.
 */
  function createTable($destroy = false) {  
    if (is_file($this->filename) && !$destroy) {
      show_warning("createTable (".$this->path.", ".$this->table.") > The file [".$this->filename."] already exists.");
      return false;
    } 
    
    if ($this->containsfields && (count($this->fields) === 0)) {
      show_error("createTable (".$this->path.", ".$this->table.") > You must define some fields (fields) for the table.");
      return false;
    }
    $filedata = $this->fieldsItem();
        
    $this->writeFile($filedata);
    
    $this->data = array();
    $this->ndata = 0;
    $this->open = true;
    return true;
  }  

/**
 * Access the file containing the table. Stock it in memory.
 *
 * @access	public
 * @return	true if the table is opened.
 */
  function openTable() {    
    if (!is_file($this->filename) || !is_readable($this->filename)) {
      show_error("openTable (".$this->path.", ".$this->table.") > The file [".$this->filename."] is not valid or can't be read.");
      return false;
    } 
    
    ini_set('auto_detect_line_endings', true); // for MAC and Unix compatibility (NL and CR)
    $this->data = array_map('remove_spaces', file($this->filename));    
        
    // take the header with the fields
    if ($this->containsfields === true) {
      $fieldsitem = array_shift($this->data);
      // TO BE CORRECT: insert a check for valid fields.
      // $fieldsitem = strtolower($fieldsitem);      
      $this->fields = explode($this->separator, $fieldsitem);
    }
    
    if ($this->data === NULL || $this->data === "") 
      $this->ndata = 0;
    else
      $this->ndata = count($this->data);
    
    $this->open = true;
    return true;
  }

  // 
  function fieldsItem() {
    if ($this->containsfields === true) {
      $fieldsitem = implode($this->separator, $this->fields);
      return $fieldsitem."\n";
    }
    else 
      return "";
  }
  
  //
  function writeFile($filedata, $writable_check = false) {
  
    if ($writable_check) 
      if (!is_writable($this->filename)) {
        show_error("(".$this->path.", ".$this->table.") > The file [".$this->filename."] is not writable.");
        return false;
      } 
    
    if (!$fhandle = fopen($this->filename, "w")) {
      show_error("(".$this->path.", ".$this->table.") > The file [".$this->filename."] can't be opened.");
      return false;
    }
    if (flock($fhandle, LOCK_EX)) { // do an exclusive lock     
      if (fwrite($fhandle, $filedata) === FALSE) {
        show_error("(".$this->path.", ".$this->table.") > The file [".$this->filename."] can't be written.");
	flock($fhandle, LOCK_UN); // release the lock
        fclose($fhandle);
        return false;
      }
      flock($fhandle, LOCK_UN); // release the lock
      fclose($fhandle);        
      return true;
    } else {
      show_error("(".$this->path.", ".$this->table.") > The file [".$this->filename."] can't be locked.");
      return false;
    }
  }

/**
 * Overwrite all the data of the table.
 *
 * @access	public
 * @param	a full text with the data of all the table.
 * @return	true if the overwrite is successful.
 */
  function stockData($data = UNDEFINED) {      
    $this->data = $data;    

    if ($this->data === NULL || $this->data === "") {
      $this->data = array();
      $this->ndata = 0;
    }
    else
      $this->ndata = count($this->data);

    $this->open = true;
    return true;
  }
  
/**
 * Refresh the table, writing data on the file. 
 *
 * @access	public
 * @return	true if the refreshing is successful.
 */
  function refreshTable() {    
    if ($this->open === false) return false;
      
    if (!is_file($this->filename) || !is_writeable($this->filename)) {
      show_error("refreshTable (".$this->path.", ".$this->table.") > The file [".$this->filename."] is not valid or can't be written.");
      return false;
    } 

    $filedata = "";
    
    $filedata = $this->fieldsItem();
    
    for ($i=0; $i < $this->ndata; $i++) {
      $filedata .= $this->data[$i]."\n"; // Unix endofline
    }

    $this->writeFile($filedata, true);
    
    return true;
  }  

/**
 * Print the table 
 *
 * @access	public
 * @return	true if the printing is successful.
 */
  function printTable() {
    if ($this->open === false) return false;

    print("<p>Table <strong>".$this->table."</strong><br/>\n");
    print($this->ndata." elements.</p>\n");
    
    print("<table>\n");
    print("<tr>\n");
    for ($j = 0; $j < count($this->fields); $j++) {
      print("  <td style='vertical-align: top; padding: 5px;'>\n");
      print("  <strong>".$this->fields[$j]."</strong>\n");
      print("  </td>\n");
    }        
    print("</tr>\n");      
    for ($i = 0; $i < count($this->data); $i++) {
      print("<tr>\n");
   
      $row = Item2Array($this->data[$i], UNDEFINED, $this->separator);
      for ($j = 0; $j < count($row); $j++) {
        print("  <td style='vertical-align: top; padding: 5px;'>\n");
        print("  ".$row[$j]);
        print("  </td>\n");
      }
      print("</tr>\n");
    }
    
    print("</table>\n");
  }

/**
 * Order the table (or part of it). Return an array of index, with or without the ordered values.
 *
 * @access	public
 * @param	string	the sorting field
 * @param	bool	true if ascending, false descending order
 * @param	array	a array, list of id (integer, line numbers)
 * @param	bool	true if values have to be linked to the result
 * @return	array	an ordered list of id
 */
  function order($field = null, $asc = true, $listid = null, $withvalues = false) {
    if ($this->open === false) return false;

    $field = strtolower($field );
    if ($field === null || $field === "" || ($field!="id" && !in_array($field, $this->fields)))
      return false;

    if (is_array($listid) && count($listid) === 0)
      return $listid;
    
    // for each element translate it in a array
    if ($listid === null) {
      for ($i = 0; $i < $this->ndata; $i++) {
        $item = $this->data[$i];
        $array = item2Array($item, $this->fields, $this->separator);      
        $array['id'] = $i + 1;
        $orderedarray[$i+1] = $array[$field]; // strtolower($array[$field]);
      }
    } else {
    
      for ($i = 0; $i < count($listid); $i++) {
        $item = $this->data[$listid[$i] - 1];
        $array = item2Array($item, $this->fields, $this->separator);      
        if ($field == 'id') 
          $orderedarray[$listid[$i]] = $listid[$i];
        else 
          $orderedarray[$listid[$i]] = $array[$field]; // strtolower($array[$field]);
      }    
    }
    
    // order the array
    if ($asc) asort($orderedarray);
    else arsort($orderedarray);    

    reset($orderedarray);

    $listordered = array_keys($orderedarray);

    if ($withvalues) {
      for ($i = 0; $i < count($listordered); $i ++) {
        $fulllist[$i]['id'] = $listordered[$i];
        $fulllist[$i][$field] = $orderedarray[$listordered[$i]];
      }      
      return $fulllist;      
    }
    else
      return $listordered;
  }

/**
 * Purge the array from the elements with the same value in a field. Return an array of index, with or without the ordered values.
 *
 * @access	public
 * @param	string	the distinguing field
 * @param	array	a array, list of id (integer, line numbers)
 * @return	array	a list of id
 */ 
  function distinct($field = null, $listid = null) {
    if ($this->open === false) return false;
    
    $field = strtolower($field);
    if ($field === null || $field === "" || ($field!="id" && !in_array($field, $this->fields)))
      return false;
      
    if ($listid === null) {
      $listid = array_keys($this->data);
      $offset = 0;
    }
    else 
      $offset = 1;
   
    $distinct = array();
    
    // for each element 
    for ($i = 0; $i < count($listid); $i++) {
      // translate it in a array
      $item = $this->data[$listid[$i] - $offset];
      $array = Item2Array($item, $this->fields, $this->separator);
      $array['id'] = $listid[$i];        
                
      // check if it has been added before
      $found = false;
      for ($j = 0; ($j < count($distinct)) && !$found; $j++) 
	if ($array[$field] === $distinct[$j])
          $found = true;
	
      // if not add it to the list.
      if (!$found) 
        $distinct[$j] = $array[$field];
    }
    
    return $distinct;
  } 

/**
 * Select a part of the table. The condition is given by the evaluation of the $where string
 *
 * @access	public
 * @param	string	the search condition
 * @param	bool	if true, names of the variables in the condition are in the form "col"+number of the associated table column
 * @return	array	a list of id
 */ 
  function select($where = "", $raw = false) {
    if ($this->open === false) return false;

    $select = array();
    
    if ($where === "" || $where == "1") // select all the table
      for ($i = $this->ndata - 1; $i >= 0; $i--) 
        $select[] = $i + 1; // changing from index to id
    else {
      // for each element translate it in a array
      for ($i = $this->ndata - 1; $i >= 0; $i--) {
        $item = $this->data[$i];
        if (!$raw) $array = Item2Array($item, $this->fields, $this->separator);
        else $array = Item2Array($item, null, $this->separator);
	
        for ($j = 0; $j < count($this->fields); $j++) {
          // define variables called as "col"+numberofcol 
          // with the values in the table        
          if ($raw) {
	        $var = "col".($j+1);
            $$var = $array[$j];
          }
          // define variables called as the fields 
          // with the values in the table
	  else {
            $field = $this->fields[$j];
            $$field = $array[$field]; 
          }	    
        }
        
        $id = $i + 1; // changing from index to id        
        
        // if the condition is satisfied than add it to the list
        eval("\$condition = ($where);");
        if ($condition) {
          $select[] = $id;
        }
      }
    }
    
    return $select;
  } 
  
  function rawselect($where) {
     return $this->select($where, true);
  }

  function checkId($id = null) {
    if ($id === null || !is_integer((int)$id) || $id === 0 || $id > $this->ndata) {
      show_error("(".$this->path.", ".$this->table.") > Id [$id] not valid.");
      return false;
    }
    return true;
  }
    
/**
 * Read an Element from the table. Return it as an array.
 *
 * @access	public
 * @param	integer	the id of the requested element
 * @return	array	the element with all data in a array.
 */ 
  function readElement($id = null) {
    if ($this->open === false) return false;
    if (!$this->checkId($id)) return false;
      
    $elem = Item2Array($this->data[$id - 1], $this->fields, $this->separator);
    $elem['id'] = $id;    
    
    return $elem;   
  }

/**
 * Add an Element in the table. Return its id.
 *
 * @access	public
 * @param	array	the element in a array form
 * @return	integer	the id of the new element
 */ 
  function addElement($array) {    
    if ($this->open === false) return false;
    
    $id = $this->ndata + 1;   
    $this->data[$this->ndata] = array2Item($array, $this->fields, $this->separator); 
    $this->ndata = $id;

	/* //Non-decreasing order
	for ($j=1; $j < count($array); $j++) {
        $field = $array[$j];
        $i = $j - 1;

        while($i >= 0 and $array[$i] > $field) {
                $array[$i + 1] = $array[$i];
                $i = $i - 1;
        }

        $array[$i + 1] = $field;
	}

	//Non-increasing order
	for ($j=1; $j < count($array); $j++) {
        $field = $array[$j];
        $i = $j - 1;

        while($i >= 0 and $array[$i] < $field) {
                $array[$i + 1] = $array[$i];
                $i = $i - 1;
        }

        $array[$i + 1] = $field;
	} */

    $this->refreshTable();
    
    return $id;
  }

/**
 * Delete an Element from the table. Return true if delete has been successful.
 *
 * @access	public
 * @param	integer	the id of the element to be deleted
 * @return	bool	true if successful
 */ 
  function deleteElement($id = UNDEFINED) {
    if ($this->open === false) return false;
    if (!$this->checkId($id)) return false;
    
    if ($this->concrete_delete) {    
      // shift all the elements
      for($i = $id - 1; $i < $this->ndata - 1; $i++) {
        $this->data[$i] = $this->data[$i+1];
      }
      unset($this->data[$i]);
    }
    else {
      $this->data[$i] = DELETED;
    }
         
    $this->ndata = $this->ndata - 1;
    
    return $this->refreshTable();
  }

/**
 * Modify an Element in the table. Return true if modifying has been successful.
 *
 * @access	public
 * @param	integer	the id of the element to be modified
 * @param	array	the element in a array form
 * @return	bool	true if successful
 */ 
  function modifyElement($id, $array) {
    if ($this->open === false) return false;
    if (!$this->checkId($id)) return false;

    // read the previous value, and the given value 
    $oldarray = item2Array($this->data[$id - 1], $this->fields, $this->separator);
            
    // if a field of the new array is UNDEFINED take the previous value
    for ($i = 0; $i < count($this->fields); $i++) {
      $field = $this->fields[$i];

      if (array_key_exists($field, $array)) {
        if ($array[$field] === UNDEFINED) { // WARNING: boolean have a different handling!
          $newarray[$field] = $oldarray[$field];
        } else
          $newarray[$field] = $array[$field];
      } else $newarray[$field] = $oldarray[$field];

    }

    $this->data[$id - 1] = Array2Item($newarray, $this->fields, $this->separator);
    return $this->refreshTable();
  }
    
  function dropColumn($columnname = NULL) {
     $pos = array_search($columnname, $this->fields);
     
     if ($pos === false)
       return false;
     
     // Purge not useful fields from header
     $newfields = $this->fields;
     unset($newfields[$pos]); 
     
    // Purge not useful fields from data
    for ($i = 0; $i < 1 /* $this->ndata */; $i++) {
      $element = item2Array($this->data[$i], $this->fields, $this->separator);
      unset($element[$columnname]); 
      $this->data[$i] = Array2Item($element, $newfields, $this->separator);
    }
    
    // Copy the new header
    $this->fields = $newfields;
    return $this->refreshTable();
  }    
  
  function truncate() {
    $this->data = array();
    $this->ndata = 0;
    
    return $this->refreshTable();
  }      
    
}

//////////////////////////////////////////////////////////////////////
/// String handling functions
//////////////////////////////////////////////////////////////////////

/**
 * Transform an array into an item (i.e. a row of the plain text table as string)
 *
 * @access	public
 * @param	array	input array
 * @param	array	fields array  
 * @param	string	field separator
 * @return	string	the input array converted in a string as row of the table
 */
function array2Item($array, $fields = UNDEFINED, $separator = FIELD_SEPARATOR) {
  $content = "";
    
  if ($fields == UNDEFINED) {
    for ($i = 0; $i < count($array); $i++) {
      if ($i != 0)
	    $content .= $separator;
      if (!($array[$i] === null))
        $content .= remove_spaces($array[$i]);
        // $content .= Unicode2TxtDB($array[$i]);
    }
  } else {
    for ($i = 0; $i < count($fields); $i++) {
      if ($i != 0)
	  $content .= $separator;
      if (!($array[$fields[$i]] === null))
        $content .= remove_spaces($array[$fields[$i]]);
        // $content .= Unicode2TxtDB($array[$fields[$i]]);        
    }
  }
    
  return $content;
}

/**
 * Convert an item (i.e. a row of the plain text table as string) in an array
 *
 * @access	public
 * @param	string	a row of the table a string
 * @param	array	fields (table fields) array  
 * @param	string	field separator
 * @return	array	the input row converted in an array
 */
function item2Array($item, $fields = UNDEFINED, $separator = FIELD_SEPARATOR) {
  if ($fields == UNDEFINED) {
    $fields = array();
    $array = explode($separator, $item);
    for ($i = 0; $i < count($array); $i++)
      array_push($fields, $i);
  } else {
    $tmparray = explode($separator, $item);

    for ($i = 0; $i < count($fields); $i++) {
      $array[$fields[$i]] = remove_spaces($tmparray[$i]);
    }
  }  
  // return array_map('TxtDB2Unicode', $array);
  return $array;
}  
