#!/usr/bin/python
import MySQLdb

db = MySQLdb.connect(host="localhost",
                     user="root",
                     passwd="",
                     db="gow")
cursor = db.cursor()

cursor.execute("SELECT * FROM location")

# db.commit()

numrows = cursor.rowcount

for x in range(0, numrows):
    row = cursor.fetchone()
    print row[0], "-->", row[1]

db.close()
