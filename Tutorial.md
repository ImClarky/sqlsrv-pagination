###SQLSRV-Pagination Tutorial  
  
This is a brief tutorial on how to get up and running using the SQLSRV-pagination plugin  
***********

#####Step 1 - Include `paginator.php` Class  
Firstly we must include the Class into our PHP file. Like so:
```php
include 'path/to/paginator.php';
```
***********

#####Step 2 - Initialise an Instance and Other Variables  
We how have to initialise the `Paginator` class:
```php
$paginator = new Paginator($conn, "dbo", "members", "lastname");
```
The Paginator constructor takes 4 parameters:  

1. `$conn` - This is your database connection
2. `"dbo"` - This is your schema name
3. `"members"` - This is your table name that you want to query
4. `"lastname"` - This is the column that you want to order the results by

There are also 2 variables that need to be defined. Those are: 

1. `$limit` - the maximum number of records on one page
2. `$page` - the current page number

These are to be initalised like so:

```php
$limit = (isset($_GET['limit'])) ? $_GET['limit'] : 10;
$page = (isset($_GET['page'])) ? $_GET['page'] : 1;
```
***********

#####Step 3 - Retrieve and Display Data
Now its time to display some data:

```php
if($paginator->getTotalRows() != 0){
  $results = $paginator->getDataSet($limit, $page);
  if(!is_null($results){
    foreach($results->dataset as $row){
      // do whatever
      // $row is an associative array
      // e.g. echo $row['firstname'];
    }
  }
} else {
  echo "There is no data to display";
}
```

The `getDataSet()` function takes in 2 parameters. These are:  

1. `$limit`
2. `$page`

**Note:** The parametes have default values of `10` and `1` respectfully
***********
#####Step 4 - Display Page Links
Finally lets display those links:
```php
echo $paginator->getPageLinks($links, $links_class);
```

The `getPageLinks()` function takes in 2 parameters. These are:

1. `$links` - The number of links either side of the selected / active link  
  So for example `$links = 3` would look like:  
    [prev] [1] ... [4] [5] [6] **[7]** [8] [9] [10] ... [2451] [next]  
      
2. `$links_class` - The CSS class name that the wrapper div around the links will have

**Note:** The parametes have default values of `2` and `pagination` respectfully.


***********

**That's it, we're done!**  
You should now have a fully-operating Pagination system. Have a look at the [Examples Folder](https://github.com/ImClarky/SQLSRV-Pagination/tree/readme-edits/Examples) for more examples of the plugin.
