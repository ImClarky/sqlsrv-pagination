# SQLSRV-Pagination

SQLSRV-Pagination is a simple pagination plugin for PHP using the SQLSRV extension. This plugin allows for dynamic pagination with little T-SQL knowledge.  

***Note:*** *This plugin is aimed at SQL Server 2008 R2 and above*

####Requirements
Not a lot at all:
+ [SQLSRV extension for PHP](https://www.microsoft.com/en-us/download/details.aspx?id=20098) PHP 5.4+ required
+ [Microsoft ODBC Driver 11 for SQL Server](https://www.microsoft.com/en-us/download/details.aspx?id=36434)
+ SQL Server 2008 R2 and above
  

All that is required in the code is:  

1. Valid Database connection using the SQLSRV extension
2. Schema name
3. Table name
4. Column name to order by
5. Page number
6. Number of records per page

See the [Tutorial](https://github.com/ImClarky/SQLSRV-Pagination/blob/master/Tutorial.md) for more details  

####Licence
MIT Licence

