-- Execute this script in your SQL Server to create the Stored Procedure
-- Change the parameter type values to your needs i.e. @schema nvarchar(15) -> nvarchar(30)
-- The values currently are just placeholders deemed large enough

CREATE PROCEDURE [dbo].[GetSectionFromTable] 
	@schema nvarchar(15),
	@table nvarchar(100),
	@orderCol nvarchar(100),
	@limit int,
	@page int
	
	AS
		DECLARE @sql nvarchar(1000)
		
		SELECT @sql = 
		N'
			SELECT * FROM
				(SELECT *, ROW_NUMBER() over(ORDER BY '+ QUOTENAME(@orderCol) +' DESC) as RowNum
				FROM '+ QUOTENAME(@schema) + '.' + QUOTENAME(@table) +'
				) as [table]
			WHERE
				[table].[RowNum] BETWEEN (('+ CAST(@page as nvarchar(3)) +' - 1) * '+ CAST(@limit as nvarchar(3)) +') + 1 AND '+ CAST(@limit as nvarchar(3)) +' * ('+ CAST(@page as nvarchar(3)) +') 
			ORDER BY
				'+ QUOTENAME(@orderCol) +' DESC
		'
		
		
		EXEC sp_executesql @sql
		
GO
