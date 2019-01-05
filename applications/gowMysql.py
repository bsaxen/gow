#!/usr/bin/python
#=============================================
# File.......: gowMysql.py
# Date.......: 2019-01-05
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
import sys

if len (sys.argv) != 2 :
    print "Usage: python gowMysql.py your-configuration.txt"
    sys.exit (1)

config_file = sys.argv[1]
print "Configuration used: " + config_file

#=============================================
# Configuration
#=============================================
cUrl        = 'gow.simuino.com'
cTopic      = 'kvv32/test/temperature/0'
cParam      = 'temp1'
cDbHost     = '192.168.1.85'
cDbName     = 'gow'
cDbUser     = 'folke'
cDbPassword = 'something'
cDbTableName = 'something'
#===================================================
def readConfiguration():
    global cUrl
    global cTopic
    global cParam
    global cDbHost
    global cDbName
    global cDbUser
    global cDbPassword
    global cDbTableName
    try:
        fh = open(config_file, 'r')
        for line in fh:
            #print line
            word = line.split()
            if word[0] == 'url':
                cUrl    = word[1]
                print cUrl
            if word[0] == 'topic':
                cTopic  = word[1]
                print cTopic
            if word[0] == 'param':
                cParam  = word[1]
                print cParam
            if word[0] == 'host':
                cDbHost = word[1]
                print cDbHost
            if word[0] == 'database':
                cDbName = word[1]
                print cDbName
            if word[0] == 'user':
                cDbUser = word[1]
                print cDbUser
            if word[0] == 'pswd':
                cDbPassword  = word[1]
                print cDbPassword
            if word[0] == 'tablename':
                cDbTableName  = word[1]
                print cDbTableName
        fh.close()
    except:
        print "Configuration file not found - configuration.txt template created"
        fh = open('configuration.txt', 'w')
        fh.write('url        gow.simuino.com\n')
        fh.write('topic      kvv32/test/temperature/0\n')
        fh.write('param      temp1\n')
        fh.write('host       192.168.1.85\n')
        fh.write('database   gow\n')
        fh.write('user       folke\n')
        fh.write('pswd       hm\n')
        fh.write('tablename  mytable\n')
        fh.close()
        sys.exit (1)
    return
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
    global cDbHost
    global cDbName
    global cDbUser
    global cDbPassword
    print xTable + xPar + str(xValue)
    db = MySQLdb.connect(host=cDbHost,user=cDbUser,db=cDbName)
    print "a1"
    cursor = db.cursor()
    print "a2"
    sql = "CREATE TABLE IF NOT EXISTS " + xTable + " (id int(11) NOT NULL AUTO_INCREMENT,value float,ts timestamp, PRIMARY KEY (id))"
    cursor.execute(sql)
    print "a3"
    sql = "INSERT INTO "+ xTable + " (`id`, " + xPar + ", `ts`) VALUES (NULL," + str(xValue) + ", CURRENT_TIMESTAMP)"
    cursor.execute(sql)
    print "a4"
    db.commit()
    print "a5"
    db.close()

#=============================================
# setup
#=============================================
readConfiguration()
#=============================================
# loop
#=============================================
while True:
    print cTopic
    url = 'http://' + cUrl + '/' + cTopic + '/device.json'
    print url
    period = float(gowReadJsonMeta(url,'period'))
    print period
    x      = float(gowReadJsonPayload(url,cParam))
    print x
    gowMysqlInsert(cDbTableName,'value',x)
    print "sleep " + str(period)
    time.sleep(period)
