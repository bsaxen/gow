#!/usr/bin/python
#=============================================
# File.......: gowMysql.py
# Date.......: 2019-01-06
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
print "Configuration used: " + "x"+config_file+"x"

#=============================================
# Configuration
#=============================================
cDbHost     = '192.168.1.85'
cDbName     = 'gow'
cDbUser     = 'folke'
cDbPassword = 'something'
c_url   = []
c_topic = []
c_table = []
c_param = []
#===================================================
def readConfiguration():
    global cDbHost
    global cDbName
    global cDbUser
    global cDbPassword
    global c_url
    global c_topic
    global c_table
    global c_param

    n = 0
    try:
        print config_file
        fh = open(config_file, 'r')
        for line in fh:
            #print line
            word = line.split()
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
            if word[0] == 'stream':
                c_url.append(word[1])
                c_topic.append(word[2])
                c_table.append(word[3])
                c_param.append(word[4])
                print str(n) + " " + c_topic[n]
                n += 1
        fh.close()
    except IOError:
        print "Configuration file not found " + config_file + " - configuration.txt template created"
        fh = open('configuration.txt', 'w')
        fh.write('host       192.168.1.85\n')
        fh.write('database   gow\n')
        fh.write('user       folke\n')
        fh.write('pswd       hm\n')
        fh.write('stream     url topic table parameter\n')
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
    url = 'http://' + c_url[0] + '/' + c_topic[0] + '/device.json'
    print url
    period = float(gowReadJsonMeta(url,'period'))
    print period
    x      = float(gowReadJsonPayload(url,cParam))
    print x
    #gowMysqlInsert(cDbTableName,'value',x)
    print "sleep " + str(period)
    time.sleep(period)
