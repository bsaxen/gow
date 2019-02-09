# =============================================
# File: gowLib.py
# Author: Benny Saxen
# Date: 2019-02-09
# Description: GOW python library
# =============================================
import MySQLdb
import urllib
import urllib2
import time
import datetime
import random
import string
import json

class datastream:
	d_topic = ''
	d_no = 0
	d_dev_ts = ""
	d_sys_ts = ""
	d_wifi_ss = 0
	
	d_value = 0.0


class configuration:
	c_title      = 'Configuration Title'
	c_tags       = 'tag1,tag2,tag3'
	c_desc       = 'some_description'
	c_url        = 'gow.simuino.com'
	c_server_app = 'gowServer.php'
	c_platform   = 'python'
	c_period     = 10.0
	c_wrap       = 999999
	c_topic1     = "topic1"
	c_topic2     = "topic2"
	c_topic3     = "topic3"
	c_topic4     = "topic4"
	c_action1    = "action1"
	c_action2    = "action2"
	c_action3    = "action3"
	c_action4    = "action4"
	c_payload1   = "{}"
	c_payload2   = "{}"
	c_payload3   = "{}"
	c_payload4   = "{}"

	# Heater algorithm
	c_mintemp = 0.0
   	c_maxtemp = 0.0
   	c_minheat = 0.0
   	c_maxheat = 0.0
   	c_x_0     = 0.0
   	c_y_0     = 0.0
   	c_relax   = 4.0
	c_minsmoke = 0.0
   	c_minsteps  = 0
   	c_maxsteps  = 0
   	c_defsteps  = 0
   	c_maxenergy = 0

	# database access
	c_dbhost     = '192.168.1.85'
	c_dbname     = 'gow'
	c_dbuser     = 'myuser'
	c_dbpassword = 'mypswd'

	# datastreams subscriptions
	c_ds_uri = []
	c_ds_topic = []
	# Database tables and parameters
	c_ds_table = []
	c_ds_param = []
	c_nds = 0

	# Image
	c_image_user   = 'folke'
	c_image_url    = 'gow.test.com'
	c_image_path   = 'images_dir'
	c_image_prefix = 'some'
	c_image_name   = 'any'

#=====================================================
def lib_buildUrl(uri,topic,dynstat):
	url =  'http://' + uri + '/' + topic + '/' + dynstat +'.json'
	return url
#=====================================================
def lib_init_history(fname):
    try:
        f = open(fname,'w')
        f.write(fname)
        f.write('\n')
        f.close()
    except:
        print "ERROR init file " + fname
    return
#=====================================================
def lib_writeFile(fname,message,ts):
    try:
        f = open(fname,'a')
	if ts == 1:
		f.write(datetime.datetime.now().strftime("%y-%m-%d %H:%M:%S")+" ")
        f.write(message)
        f.write('\n')
        f.close()
    except:
        print "ERROR write to file " + fname
    return
#=====================================================
def lib_log(application,message):
	msg = application + " " + message
	lib_writeFile('gow.log',msg,1)
	return
#===================================================
def lib_common_action(c1,order):
	if ":" in order:
		p = order.split(':')
		q = p[1].split(",")
		m = len(q)
		if m == 1:
			if q[0] == 'test':
				print 'test1'
		if m == 2:
			if q[0] == 'period':
				c1.c_period = q[1]
			if q[0] == 'action':
				c1.c_action = q[1]
			if q[0] == 'topic':
				c1.c_topic1 = q[1]
			if q[0] == 'desc':
				c1.c_desc = q[1]
		if m == 3:
			if q[0] == 'test':
				print 'test3'

#===================================================
def lib_evaluateAction( action):
	print action
#===================================================
def lib_readConfiguration(confile,c1):
	try:
		c1.c_nds = 0
		fh = open(confile, 'r')
		for line in fh:
			#print line
			if line[0] != '#':
				word = line.split()
				if word[0] == 'c_title':
					c1.c_title         = word[1]
				if word[0] == 'c_tags':
					c1.c_tags         = word[1]
				if word[0] == 'c_desc':
					c1.c_desc         = word[1]
				if word[0] == 'c_url':
					c1.c_url         = word[1]
				if word[0] == 'c_app':
					c1.c_server_app     = word[1]
				if word[0] == 'c_period':
					c1.c_period         = word[1]
				if word[0] == 'c_platfrom':
					c1.c_patform             = word[1]
				if word[0] == 'c_wrap':
					c1.c_wrap           = word[1]
				if word[0] == 'c_topic1':
					c1.c_topic1         = word[1]
				if word[0] == 'c_topic2':
					c1.c_topic2         = word[1]
				if word[0] == 'c_topic3':
					c1.c_topic3         = word[1]
				if word[0] == 'c_topic4':
					c1.c_topic4         = word[1]
				if word[0] == 'c_action1':
					c1.c_action1         = word[1]
				if word[0] == 'c_action2':
					c1.c_action2         = word[1]
				if word[0] == 'c_action3':
					c1.c_action3         = word[1]
				if word[0] == 'c_action4':
					c1.c_action4         = word[1]
				if word[0] == 'c_payload1':
					c1.c_payload1         = word[1]
				if word[0] == 'c_payload2':
					c1.c_payload2         = word[1]
				if word[0] == 'c_payload3':
					c1.c_payload3         = word[1]
				if word[0] == 'c_payload4':
					c1.c_payload4         = word[1]

				# Heater algorithm
				if word[0] == 'c_mintemp':
					c1.c_mintemp          = word[1]
				if word[0] == 'c_maxtemp':
					c1.c_maxtemp          = word[1]
				if word[0] == 'c_minheat':
					c1.c_minheat          = word[1]
				if word[0] == 'c_maxheat':
					c1.c_maxheat          = word[1]
				if word[0] == 'c_x_0':
					c1.c_x_0              = word[1]
				if word[0] == 'c_y_0':
					c1.c_y_0              = word[1]
				if word[0] == 'c_relax':
					c1.c_relax            = word[1]
				if word[0] == 'c_minsmoke':
					c1.c_minsmoke         = word[1]
				if word[0] == 'c_minsteps':
					c1.c_minsteps         = word[1]
				if word[0] == 'c_maxsteps':
					c1.c_maxsteps         = word[1]
				if word[0] == 'c_defsteps':
					c1.c_defsteps         = word[1]
				if word[0] == 'c_maxenergy':
					c1.c_maxenergy        = word[1]

				# Database access
				if word[0] == 'c_dbhost':
					c1.c_dbhost         = word[1]
				if word[0] == 'c_dbname':
					c1.c_dbname         = word[1]
				if word[0] == 'c_dbuser':
					c1.c_dbuser         = word[1]
				if word[0] == 'c_dbpassword':
					c1.c_dbpassword      = word[1]

				if word[0] == 'c_stream':
					c1.c_ds_uri.append(word[1])
					c1.c_ds_topic.append(word[2])
					c1.c_ds_table.append(word[3])
					c1.c_ds_param.append(word[4])
					c1.c_nds += 1

				# Image
				if word[0] == 'c_image_user':
					c1.c_image_user      = word[1]
				if word[0] == 'c_image_user':
					c1.c_image_url       = word[1]
				if word[0] == 'c_image_url':
					c1.c_image_path      = word[1]
				if word[0] == 'c_image_path':
					c1.c_image_path      = word[1]
				if word[0] == 'c_image_name':
					c1.c_image_name      = word[1]
			else:
				print line
		fh.close()
	except:
		fh = open(confile, 'w')
		fh.write('c_title    Configuration Title\n')
		fh.write('c_url      gow.simuino.com\n')
		fh.write('c_tags     tag1,tag2,tag3\n')
		fh.write('c_desc     some_description\n')
		fh.write('c_server   gowServer.php\n')
		fh.write('c_period   10.0\n')
		fh.write('c_platform python\n')
		fh.write('c_wrap     999999\n')

		fh.write('c_topic1   topic1\n')
		fh.write('c_topic2   topic2\n')
		fh.write('c_topic3   topic3\n')
		fh.write('c_topic4   topic4\n')

		fh.write('c_payload1  {}\n')
		fh.write('c_payload2  {}\n')
		fh.write('c_payload3  {}\n')
		fh.write('c_payload4  {}\n')

		fh.write('c_action1  {}\n')
		fh.write('c_action2  {}\n')
		fh.write('c_action3  {}\n')
		fh.write('c_action4  {}\n')

		fh.write('c_mintemp      -7\n')
		fh.write('c_maxtemp      15\n')
		fh.write('c_minheat      25\n')
		fh.write('c_maxheat      40\n')
		fh.write('c_x_0          0\n')
		fh.write('c_y_0          36\n')
		fh.write('c_relax        4.0\n')
		fh.write('c_minsmoke     27\n')
		fh.write('c_minsteps     5\n')
		fh.write('c_maxsteps     40\n')
		fh.write('c_defsteps     30\n')
		fh.write('c_maxenergy    4.0\n')

		fh.write('c_dbhost       192.168.1.85\n')
		fh.write('c_dbname       gow\n')
		fh.write('c_dbuser       folke\n')
		fh.write('c_dbpassword   something\n')
		fh.write('c_stream       uri topic table param\n')

		fh.write('c_image_user   folke\n')
		fh.write('c_image_url    gow.test.com\n')
		fh.write('c_image_path   images\n')
		fh.write('c_image_prefix some\n')
		fh.write('c_image_name   any\n')
		fh.close()
		print "Configuration file created: " + confile
		print "Edit your configuration and restart the application"
		exit()
	return

#=============================================
def lib_consumeDatastream(d,url,par):
#=============================================
	j = lib_readDatastream(url)
	n = lib_decodeDatastream(j,'no',1)
	d.d_sys_ts = lib_decodeDatastream(j,'sys_ts',1)
	if n < d.d_no:
		msg = "Datastream sequence number out of order " + str(d.d_topic)
		log('lib',msg)
	if n > d.d_no or n == 1:
		x = lib_decodeDatastream(j,par,0)
		d.d_value = x
		d.d_no = n
	else:
		x = d.d_value
	return x
#=============================================
def lib_decodeDatastream(j,par,meta):
#=============================================
	if meta == 1:
		x =  j['gow'][par]
	else:
		x =  j['gow']['payload'][par]
	return x
#=============================================
def lib_readDatastream(url):
#=============================================
    r = urllib2.urlopen(url)
    j = json.load(r)
    return j
#=============================================
def lib_readJsonMeta(url,par):
#=============================================
    r = urllib2.urlopen(url)
    j = json.load(r)
    x =  j['gow'][par]
    return x
#=============================================
def lib_readJsonPayload(url,par):
#=============================================
	now = str(datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S"))
	r = urllib2.urlopen(url)
	j = json.load(r)
	ts=j['gow']['sys_ts']
	#period=j['gow']['period']
	xts1 = time.mktime(datetime.datetime.strptime(ts, "%Y-%m-%d %H:%M:%S").timetuple())
	xts2 = time.mktime(datetime.datetime.strptime(now, "%Y-%m-%d %H:%M:%S").timetuple())
	diff = xts2 - xts1
	#print str(period) + " " + str(diff)
	#old = 0
	#if diff > period:
	#	old = 1
	#	print "old data"
	x =  j['gow']['payload'][par]
	#if old == 1:
	#	x = 123456789
	return x
#===================================================
def lib_publish_static(c1, itopic, actions ):
#===================================================
	url = c1.c_url
	server = c1.c_server_app
	data = {}
	res = '-'
	# meta data
	data['do']        = 'stat'
	data['topic']     = itopic
	data['wrap']      = c1.c_wrap
	data['dev_ts']    = datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")
	data['period']    = c1.c_period
	data['platform']  = c1.c_platform
	data['action']    = actions
	data['ssid']      = 0
	data['url']       = c1.c_url
	data['tags']      = c1.c_tags
	data['desc']      = c1.c_desc

	values = urllib.urlencode(data)
	req = 'http://' + url + '/' + server + '?' + values
	#print req
	try:
		response = urllib2.urlopen(req)
		the_page = response.read()
		#if actions == 2:
		#	print 'Message to ' + itopic + ': ' + the_page
		#lib_evaluateAction(the_page)
	except urllib2.URLError as e:
		print e.reason
	if actions == 2:
		res = the_page
	return res
#===================================================
def lib_publish_dynamic(c1, itopic, ipayload, n, actions ):
#===================================================
	url = c1.c_url
	server = c1.c_server_app
	data = {}
	res = '-'

	data['do']         = 'dyn'
	data['topic']      = itopic
	data['no']         = n
	data['wifi_ss']    = 0
	data['dev_ts']     = datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")
	# payload
	data['payload'] = ipayload

	values = urllib.urlencode(data)
	req = 'http://' + url + '/' + server + '?' + values
	#print req
	try:
		response = urllib2.urlopen(req)
		the_page = response.read()
		#if actions == 2:
		#	print 'Message to ' + itopic + ': ' + the_page
		#lib_evaluateAction(the_page)
	except urllib2.URLError as e:
		print e.reason
	if actions == 2:
		res = the_page
	return res

#===================================================
def lib_publish_log(c1, itopic, message ):
#===================================================
	url = c1.c_url
	server = c1.c_server_app
	data = {}
	res = '-'

	data['do']         = 'log'
	data['topic']      = itopic
	data['dev_ts']     = datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")
	data['log']        = message

	values = urllib.urlencode(data)
	req = 'http://' + url + '/' + server + '?' + values

	try:
		response = urllib2.urlopen(req)
		the_page = response.read()
	except urllib2.URLError as e:
		print e.reason
	return
#===================================================
def lib_placeOrder(c1, itopic, iaction):
#===================================================
	url = c1.c_url
	server = c1.c_server_app
	data = {}
	data['do']     = 'action'
	data['topic']  = itopic
	data['order']  = iaction
	data['tag']    = lib_generateRandomString()
	values = urllib.urlencode(data)
	req = 'http://' + url + '/' + server + '?' + values
	print req
	try:
		response = urllib2.urlopen(req)
	except urllib2.URLError as e:
		print e.reason
#=============================================
def lib_mysqlInsert(c1,cr,xTable,xPar,xValue):
    db = MySQLdb.connect(host=c1.c_dbhost,user=c1.c_dbuser,db=c1.c_dbname)
    cursor = db.cursor()
    if cr == 1:
        sql = "CREATE TABLE IF NOT EXISTS " + xTable + " (id int(11) NOT NULL AUTO_INCREMENT,value float,ts timestamp, PRIMARY KEY (id))"
        cursor.execute(sql)
    sql = "INSERT INTO "+ xTable + " (`id`, " + xPar + ", `ts`) VALUES (NULL," + str(xValue) + ", CURRENT_TIMESTAMP)"
    cursor.execute(sql)
    db.commit()
    db.close()

#=============================================
def lib_generateRandomString():
#=============================================
   char_set = string.ascii_uppercase + string.digits
   return ''.join(random.sample(char_set*6, 6))
#===================================================
# End of file
#===================================================
