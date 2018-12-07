#!/usr/bin/python
#=============================================
# File.......: gowMysql.py
# Date.......: 2018-12-07
# Author.....: Benny Saxen
# Description: GOW application template
#=============================================
# Libraries
#=============================================
import MySQLdb
import requests
import json
import urllib2
import time
#=============================================
# Configuration
#=============================================
cDbHost     = '192.168.1.85'
cDbName     = 'gow'
cDbUser     = 'folke'
cDbPassword = 'something'
#=============================================
def gowReadJsonParameter(url,par):
#=============================================
    r = urllib2.urlopen(url)
    j = json.load(r)
    x =  j['gow'][par]
    return x
#=============================================
def gowMysqlInsert(xTable,xPar):
#=============================================
    db = MySQLdb.connect(host=cDbHost,
                     user=cDbUser,
                     #passwd=cDbPassword,
                     db=cDbName)
    cursor = db.cursor()
    sql = "INSERT INTO `temperatur1` (`id`, `value`, `ts`) VALUES (NULL," + str(x) + ", CURRENT_TIMESTAMP)"
    cursor.execute(sql)
    db.commit()
    db.close()
#=============================================
while True:
#=============================================
    url = "http://gow.simuino.com/kvv32/test/temperature/0/doc.json"
    
    period = float(gowReadJsonParameter(url,'period'))
    x      = float(gowReadJsonParameter(url,'value'))
    
    gowMysqlInsert('temperatur1','value',x)

    
    time.sleep(period)
