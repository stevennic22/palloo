#!/usr/bin/env python
# -*- coding: utf-8 -*-
from __future__ import print_function
import httplib2, os, string, time, copy

from apiclient import discovery
import oauth2client
from oauth2client import client
from oauth2client import tools

import datetime

try:
	import argparse
	flags = argparse.ArgumentParser(parents=[tools.argparser]).parse_args()
except ImportError:
	flags = None

SCOPES = 'https://www.googleapis.com/auth/calendar.readonly'
CLIENT_SECRET_FILE = 'client_secret.json'
APPLICATION_NAME = 'Google Calendar API Python Quickstart'

def findName(eventString):
	directory = {}
	for(dirpath,dirnames,filenames) in os.walk('Ext'):
		for file in filenames:
			if(file != "rotation" and file != "oncall" and file != "phptransfer"):
				if(file in string.lower(eventString)):
					directory[file] = string.lower(eventString).find(file)
	return(min(directory, key=directory.get))

def theMainEvent(listOfEvents,events):
	if(events == 1):
		for event in listOfEvents:
			onCallInEvent = findName(event)
			return(onCallInEvent)
	elif(events == 2):
		for event,val in listOfEvents.iteritems():
			if('taking' in event or 'for' in event or 'shift' in event):
				val[1] = True
		count = 0
		for event,val in listOfEvents.iteritems():
			if(listOfEvents[event][1] == True):
				count += 1
		
		if(count == 0):
			eventDates = []
			counter = 0
			todaysDate = time.strftime("%Y-%m-%d")
			for event,val in listOfEvents.iteritems():
				eventDates[counter] = val[0]
				counter +=1
			if(eventDates[0] > eventDates[1]):
				return("Well... Do something 2")
			elif(eventDates[0] < eventDates[1]):
				return("Well... Do something 1")
			else:
				if(listOfEvents[event][0] == todaysDate):
					onCallInEvent = findName(event)
					return(onCallInEvent)
		elif(count == 1):
			for event,val in listOfEvents.iteritems():
				if(listOfEvents[event][1] == True):
					onCallInEvent = findName(event)
					return(onCallInEvent)
	else:
		return("Well... Do something 3")

def get_credentials():
	home_dir = os.path.expanduser('~')
	credential_dir = os.path.normpath("/home/steven/Desktop")
	if not os.path.exists(credential_dir):
		os.makedirs(credential_dir)
	credential_path = os.path.join(credential_dir,'calendar-python-quickstart.json')

	store = oauth2client.file.Storage(credential_path)
	credentials = store.get()
	if not credentials or credentials.invalid:
		flow = client.flow_from_clientsecrets(CLIENT_SECRET_FILE, SCOPES)
		flow.user_agent = APPLICATION_NAME
		if flags:
			credentials = tools.run_flow(flow, store, flags)
		else: # Needed only for compatibility with Python 2.6
			credentials = tools.run(flow, store)
		print('Storing credentials to ' + credential_path)
	return credentials

def main():
	"""Shows basic usage of the Google Calendar API.
	
	Source for most of this:
	https://developers.google.com/google-apps/calendar/quickstart/python
	https://developers.google.com/resources/api-libraries/documentation/calendar/v3/python/latest/calendar_v3.calendarList.html#list
	https://developers.google.com/resources/api-libraries/documentation/calendar/v3/python/latest/calendar_v3.events.html#list
	
	Creates a Google Calendar API service object and outputs a list of the next
	10 events on the user's calendar.
	"""
	credentials = get_credentials()
	http = credentials.authorize(httplib2.Http())
	service = discovery.build('calendar', 'v3', http=http)

	now = datetime.datetime.utcnow().isoformat() + 'Z' # 'Z' indicates UTC time
	#print('Getting some events')
	eventsResult = service.events().list(
        calendarId='support@sleepex.com', timeMin=now, maxResults=10, singleEvents=True,
		orderBy='startTime').execute()
	events = eventsResult.get('items', [])

	if not events:
		print('No upcoming events found.')
	else:
		finalEvent = None
		eventList = {}
		eventNum = 0
		for event in events:
			datum = time.strftime("%Y-%m-%d")
			start = event['start'].get('dateTime', event['start'].get('date'))
			if(('on-call' in string.lower(event['summary']) or 'on call' in string.lower(event['summary'])) and (start <= datum)):
				#print(start, event['summary'])
				#if('taking' in string.lower(event['summary']) or 'for' in string.lower(event['summary']) or 'shift' in string.lower(event['summary'])):
				eventList[string.lower(event["summary"])] = [start,False]
				eventNum += 1
					#global finalEvent
					#finalEvent = copy.deepcopy(event)
			#if(datum in start):
				#print("same")
			#elif(start < datum):
				#print("oooold")
		#print(eventNum)
		finalEvent = theMainEvent(eventList,eventNum)
		print(finalEvent)
		phpTransferFile = "Ext/phptransfer"
		transferFile = open(phpTransferFile,"w+")
		transferFile.write(finalEvent)
		transferFile.close()

		##print(finalEvent['summary'])
		##retName = findName(finalEvent['summary'])
		#retName = findName("Kareem Taking Amy's On-Call")
		
if __name__ == '__main__':
	main()
