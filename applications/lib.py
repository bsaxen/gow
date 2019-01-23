# =============================================
# File: lib.py
# Author: Benny Saxen
# Date: 2019-01-23
# Description: GOW python library
# =============================================
import urllib
import urllib2
import time
import datetime

class configuration:
	c_url     = 'gow.simuino.com'
	c_server_app = 'gowServer.php'
	c_hw         = 'python'
	c_period     = 10
	c_wrap       = 999999
	c_topic1     = "topic1"
	c_topic2     = "topic2"
	c_topic3     = "topic3"
	c_action1    = "action1"
	c_action2    = "action2"
	c_action3    = "action3"
#===================================================
def hello(p1):
	print "hello"
	print p1.c_wrap
#===================================================
def lib_evaluateAction( action):
	print action
#===================================================
def lib_readConfiguration(confile,c1):
	try:
		fh = open(confile, 'r')
		for line in fh:
			print line
			word = line.split()
			if word[0] == 'gs_url':
				c1.c_gs_url         = word[1]
			if word[0] == 'gs_app':
				c1.c_server_app     = word[1]
			if word[0] == 'gs_period':
				c1.c_period         = word[1]
			if word[0] == 'gs_hw':
				c1.c_hw             = word[1]
			if word[0] == 'gs_wrap':
				c1.c_wrap           = word[1]
			if word[0] == 'gs_topic1':
				c1.c_topic1         = word[1]
			if word[0] == 'gs_topic2':
				c1.c_topic2         = word[1]
			if word[0] == 'gs_topic3':
				c1.c_topic3         = word[1]
			if word[0] == 'gs_action1':
				c1.c_action1         = word[1]
			if word[0] == 'gs_action2':
				c1.c_action2         = word[1]
			if word[0] == 'gs_action3':
				c1.c_action3         = word[1]
			if word[0] == 'gs_tag1':
				c1.c_tag1         = word[1]
			if word[0] == 'gs_tag2':
				c1.c_tag2         = word[1]
			if word[0] == 'gs_tag3':
				c1.c_tag3         = word[1]
		fh.close()
	except:
		fh = open(confile, 'w')
		fh.write('gs_url      gow.simuino.com\n')
		fh.write('gs_server   gowServer.php\n')
		fh.write('gs_period   10\n')
		fh.write('gs_hw       python\n')
		fh.write('gs_wrap     999999\n')
		fh.write('gs_topic1   topic1\n')
		fh.write('gs_topic2   topic2\n')
		fh.write('gs_topic3   topic3\n')
		fh.write('gs_tag1   tag1\n')
		fh.write('gs_tag2   tag2\n')
		fh.write('gs_tag3   tag3\n')
		fh.close()
	return
#===================================================
def lib_publish(c1, itopic, ipayload, n ):
#===================================================
	url = c1.c_gs_url
	server = c1.c_server_app
	data = {}
	# meta data
	data['do']     = 'data'
	data['topic']  = itopic
	data['no']     = n
	data['wrap']   = c1.c_wrap
	data['ts']     = datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")
	data['period'] = c1.c_period
	data['hw']     = c1.c_hw
	# payload
	data['payload'] = ipayload

	values = urllib.urlencode(data)
	req = 'http://' + url + '/' + server + '?' + values
	print req
	try:
		response = urllib2.urlopen(req)
		the_page = response.read()
		print 'Message to ' + itopic + ': ' + the_page
		evaluateAction(the_page)
	except urllib2.URLError as e:
		print e.reason

#===================================================
def lib_placeOrder(c1, itopic, iaction, itag ):
#===================================================
	url = c1.c_gs_url
	server = c1.c_server_app
	data = {}
	data['do']     = 'action'
	data['topic']  = itopic
	data['order']  = iaction
	data['tag']    = itag
	values = urllib.urlencode(data)
	req = 'http://' + url + '/' + server + '?' + values
	print req
	try:
		response = urllib2.urlopen(req)
	except urllib2.URLError as e:
		print e.reason

#===================================================
# End of file
#===================================================
