#!/usr/bin/python
#=============================================
# File.......: gowMysql.py
# Date.......: 2019-01-01
# Author.....: Benny Saxen
# Description: 
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
def gowReadJsonMeta(url,par):
#=============================================
    r = urllib2.urlopen(url)
    j = json.load(r)
    x =  j['gow'][par]
    return x
#=============================================
def gowReadJsonPayload(url,par):
#=============================================
    r = urllib2.urlopen(url)
    j = json.load(r)
    x =  j['gow']['payload'][par]
    return x
#=============================================
def gowMysqlInsert(xTable,xPar,xValue):
#=============================================
    db = MySQLdb.connect(host=cDbHost,
                     user=cDbUser,
                     #passwd=cDbPassword,
                     db=cDbName)
    cursor = db.cursor()
    sql = "INSERT INTO "+ xTable + " (`id`, " + xPar + ", `ts`) VALUES (NULL," + str(xValue) + ", CURRENT_TIMESTAMP)"
    cursor.execute(sql)
    db.commit()
    db.close()
#=============================================
while True:
#=============================================
    url = "http://gow.simuino.com/kvv32/test/temperature/0/device.json"
    
    period = float(gowReadJsonMeta(url,'period'))
    x      = float(gowReadJsonPayload(url,'temp1'))
    
    gowMysqlInsert('temperatur1','value',x)

    
    time.sleep(period)
