<?php

/**
 * @author Sean Clark <seandclark94@gmail.com>
 * @license MIT
 * @license https://opensource.org/licenses/MIT
 */
class Paginator {

  /**
   *
   * @var integer Max number of records per gage
   */
  private $_limit;

  /**
   *
   * @var integer Page number
   */
  private $_page;

  /**
   *
   * @var resource Connection to SQL Server
   */
  private $_con;

  /**
   *
   * @var integer Total number of records
   */
  private $_total;

  /**
   *
   * @var string Name of the table in database
   */
  private $_table;

  /**
   *
   * @var string Name of the Schema in the database
   */
  private $_schema;

  /**
   *
   * @var string Name of the column to order by
   */
  private $_orderBy;

  /**
   * 
   * Constructor - Sets connection, schema, table, orderBy 
   * and calculates the total number of rows
   * 
   * @param resource $con Connection to your SQL Server
   * @param string $db_schema Name of the Schema in the database
   * @param type $tableName Name of the table in database
   * @param type $order_by Name of the column to order by
   */
  public function __construct($con, $db_schema, $tableName, $order_by) {
    $this->_con = $con;
    $this->_table = $tableName;
    $this->_schema = $db_schema;
    $this->_orderBy = $order_by;

    $totalRows = $this->selectAll();

    $this->_total = sqlsrv_num_rows($totalRows['data']);
  }

  /**
   * 
   * Returns the total number of records in queried table
   * 
   * @return integer The total number of records
   */
  public function getTotalRows() {
    return $this->_total;
  }

  /**
   * 
   * Retrieve data for limit and page params
   * 
   * @param integer $limit Max number of records per page - Default: 10
   * @param integer $page Page number - Default: 1
   * @return \stdClass|null Object containing captured data
   */
  public function getDataSet($limit = 10, $page = 1) {
    $this->_limit = $limit;
    $this->_page = $page;

    // Two differnet methods. See functions for details
    //$results = $this->selectSection_StoredProcedure();
    $results = $this->selectSection_Statement();


    $c = 0;
    while ($row = sqlsrv_fetch_array($results['data'], SQLSRV_FETCH_ASSOC)) {
      $resultSet[] = $row;
      $c++;
    }

    // No data causes error '$resultSet does not exist'
    // Returns NULL if this is the case
    if ($c != 0) {
      $data = new stdClass();
      $data->page = $this->_page;
      $data->limit = $this->_limit;
      $data->total = $this->_total;
      $data->dataset = $resultSet;

      return $data;
    } else {
      return null;
    }
  }

  /**
   * 
   * Create HTML for page links
   * 
   * @param integer $links The maximum number of links either side of selected page. Example: $links = 3 would output - 1 ... 4 5 6 [7] 8 9 10 ... 23
   * @param string $links_class Class applied to container div around links - Default: null
   * @return string HTML string of links
   */
  public function getPageLinks($links = 2, $links_class = "pagination") {
    // Removes unnecessary page number at the bottom 
    // when all data fits on 1 page
    if ($this->_total <= $this->_limit) {
      return;
    }

    $last = ceil($this->_total / $this->_limit);

    // use of (($this->_page - $links) > $links) instead of (($this->_page - $links) > 1)
    // to prevent unnecessary inclusion of "dots" (...)
    // Example:    1 ... 2 3 4 [5] 6 etc.
    // Same reasoning applies for $end; ($last - $links)) instead of ($last))
    $start = (($this->_page - $links) > $links) ? $this->_page - $links : 1;
    $end = (($this->_page + $links) < ($last - $links)) ? $this->_page + $links : $last;

    $pageLinks = "<div class='$links_class'>";

    if ($this->_page != 1) {
      $pageLinks .= "<a href='?limit=" . $this->_limit . "&page=" . ($this->_page - 1) . "'><span class='page-num'>prev</span></a>";
    }

    if ($start > 1) {
      $pageLinks .= "<a href='?limit=" . $this->_limit . "&page=1'><span class='page-num'>1</span></a>";
      $pageLinks .= "<a><span class='page-num dots'>...</span></a>";
    }

    for ($i = $start; $i <= $end; $i++) {
      $class = ($this->_page == $i) ? "selected" : "";
      $pageLinks .= "<a href='?limit=" . $this->_limit . "&page=" . $i . "'><span class='page-num $class'>" . $i . "</span></a>";
    }

    if ($end < $last) {
      $pageLinks .= "<a><span class='page-num dots'>...</span></a>";
      $pageLinks .= "<a href='?limit=" . $this->_limit . "&page=" . $last . "'><span class='page-num'>" . $last . "</span></a>";
    }

    if ($this->_page != $last) {
      $pageLinks .= "<a href='?limit=" . $this->_limit . "&page=" . ($this->_page + 1) . "'><span class='page-num'>next</span></a>";
    }

    return $pageLinks;
  }

  /**
   * 
   * Select all data from specified schema and table
   * 
   * @return Mixed[] Array containing SQL query responce
   */
  private function selectAll() {
    $q = "SELECT * FROM [" . $this->_schema . "].[" . $this->_table . "]";

    // array("Scrollable" => SQLSRV_CURSOR_STATIC) necessary for sqlsrv_num_rows (:70). Default (forward) cursor does not work
    // Options for "Scrollable": SQLSRV_CURSOR_STATIC or 'static' | SQLSRV_CURSOR_KEYSET or 'keyset'
    // See: https://msdn.microsoft.com/en-us/library/ee376927(v=sql.90).aspx for more details on SQLSRV cursors

    $result = sqlsrv_query($this->_con, $q, null, array("Scrollable" => SQLSRV_CURSOR_STATIC));

    if (!$result) {
      echo "Operation could not be completed.<br>";
      die(print_r(sqlsrv_errors(), true));
    } else {
      return array("data" => $result);
    }
  }

  /**
   * 
   * Get the data from the table using the page, limit and orderBy, variables
   * This is a stored procedure based method
   * Full stored procedure can be found at: [insert GitHub link]
   * 
   * @return Mixed[] Result set for page and limit
   */
  private function selectSection_StoredProcedure() {
    $q = "{call [dbo].[GetSectionFromTable](?,?,?,?,?)}";
    $params = array(
        array($this->_schema, SQLSRV_PARAM_IN),
        array($this->_table, SQLSRV_PARAM_IN),
        array($this->_orderBy, SQLSRV_PARAM_IN),
        array($this->_limit, SQLSRV_PARAM_IN),
        array($this->_page, SQLSRV_PARAM_IN)
    );

    $result = sqlsrv_query($this->_con, $q, $params);

    if (!$result) {
      echo "Operation could not be completed.<br>";
      die(print_r(sqlsrv_errors(), true));
    } else {
      // Note: This is my prefered way to return database data 
      // as sometimes I have to return OUT params with the dataset, got into the habit of formatting this way
      // return $result will work just as fine but remember to alter the sqlsrv_fetch_array to match
      return array("data" => $result);
    }
  }

  /**
   * 
   * Get the data from the table using the page, limit and orderBy, variables
   * This is a statement based method
   * 
   * @return Mixed[] Result set for page and limit
   */
  private function selectSection_Statement() {
    // As $_page and $_limit can be altered in the URL
    // They have been parametised to prevent SQL injection
    // $_table, $_schema and $_orderBy are programmed therefore should not need parameterisation
    $q = "
      DECLARE @page int = ?, @limit int = ?
      SELECT * FROM
	(SELECT *, ROW_NUMBER() over(ORDER BY [" . $this->_orderBy . "] DESC) as RowNum
	FROM [" . $this->_schema . "].[" . $this->_table . "]
	) as [table]
      WHERE
	table].[RowNum] BETWEEN ((@page - 1) * @limit) + 1 AND @limit * (@page) 
      ORDER BY
	[" . $this->_orderBy . "] DESC";

    $result = sqlsrv_query($this->_con, $q, array(&$this->_page, &$this->_limit));

    if (!$result) {
      echo "Operation could not be completed.<br>";
      die(print_r(sqlsrv_errors(), true));
    } else {
      // Note: This is my prefered way to return database data 
      // as sometimes I have to return OUT params with the dataset, got into the habit of formatting this way
      // return $result will work just as fine but remember to alter the sqlsrv_fetch_array to match
      return array("data" => $result);
    }
  }
}
