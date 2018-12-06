#!/usr/bin/python
import MySQLdb
import requests
import json
import urllib2
import time


while True:
    scUrl = "http://gow.simuino.com/kvv32/test/temperature/0/doc.json"
    print scUrl
    r = urllib2.urlopen(scUrl)


    j = json.load(r)
    x =  j['gow']['value']
    period =  float(j['gow']['period'])
    print x



    db = MySQLdb.connect(host="192.168.1.85",
                     user="folke",
                     #passwd="asdasd",
                     db="gow")
    cursor = db.cursor()

    sql = "INSERT INTO `temperatur1` (`id`, `value`, `ts`) VALUES (NULL," + str(x) + ", CURRENT_TIMESTAMP)"
    print sql
    cursor.execute(sql)

    db.commit()
    #cursor.execute("SELECT * FROM temperatur1")
    #numrows = cursor.rowcount
    #print numrows
    #for x in range(0, numrows):
    #    row = cursor.fetchone()
    #    print row[0], "-->", row[1], "-->", row[2]
    db.close()

    time.sleep(period)
