# Joomla DB Comparison tool

Compare the total records and missing tables between two database.

This tool is used to help monitor and diagnose data loss, providing
a quick snapshot of the tables with total record counts that differ
between two databases.

## Requirements

 - Joomla 2.5 >
 - Both tables must be in the same database with same access credentials
 - Second database is name is the same as the first extended with "_compare"
 
## Usage

  - Call as a CLI
  - Request via WEB (must be located within folder that employs HTTP_AUTH)

## Example Report

    DB1 Name:      user_dbname
    DB1 Tables:    120

    DB2 Name:      user_dbname_compare
    DB2 Tables:    120

    table_name                                        db1_count      db2_count      diff      

    session                                           12             7              5         
    user_usergroup_map                                40411          40407          4         
    users                                             40396          40393          3         

    table_errors                                      

    test not present in DB1
    test_renamed not present in DB2

    complete
