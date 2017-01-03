#!/usr/bin/env python
# -*- coding: utf-8 -*-
from __future__ import print_function
import httplib2, os, string, logging, datetime, json

from apiclient import discovery
import oauth2client
from oauth2client import client
from oauth2client import tools

if(os.path.isdir("LOGS") == False):
  os.makedirs("LOGS")
logFileName = os.path.normpath("LOGS\CalChecking") + datetime.datetime.now().strftime("%y%m%d%H%M%S") + ".LOG"
logging.basicConfig(format='%(levelname)s: %(message)s', filename=logFileName,level=logging.INFO)

try:
  import argparse
  flags = argparse.ArgumentParser(parents=[tools.argparser]).parse_args()
except ImportError:
  flags = None

SCOPES = "https://www.googleapis.com/auth/calendar.readonly"
CLIENT_SECRET_FILE = os.path.normpath("client_secret.json")
APPLICATION_NAME = "Google Calendar API Python Quickstart"

def findName(eventString):
  fileLoc = os.path.normpath('extensions.json')
  with open(fileLoc,'r') as extensionsFile:
    ext = json.load(extensionsFile)
  
  for users in ext["palloo"]["extensions"]:
    if (string.lower(users["name"]) in string.lower(eventString)):
      return string.lower(users["name"])

def theMainEvent(listOfEvents,events):
  if(events == 1):
    for event in listOfEvents:
      onCallInEvent = findName(event)
      return(onCallInEvent)
  elif(events == 2):
    for event,val in listOfEvents.iteritems():
      if('taking' in event or 'for' in event or 'shift' in event or 'covering' in event):
        val[1] = True
    count = 0
    for event,val in listOfEvents.iteritems():
      if(listOfEvents[event][1] == True):
        count += 1
    
    if(count == 0):
      eventDates = []
      counter = 0
      todaysDate = datetime.datetime.now().strftime("%Y-%m-%d")
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
    logging.warning(events)
    return("Well... Do something 3")

def get_credentials():
  credential_path = 'calendar-python-quickstart.json'
  store = oauth2client.file.Storage(credential_path)
  credentials = store.get()
  if not credentials or credentials.invalid:
    flow = client.flow_from_clientsecrets(CLIENT_SECRET_FILE, SCOPES)
    flow.user_agent = APPLICATION_NAME
    if flags:
      credentials = tools.run_flow(flow, store, flags)
    else: # Needed only for compatibility with Python 2.6
      credentials = tools.run(flow, store)
    logging.warning('Storing credentials to ' + credential_path)
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
  #logging.info('Getting some events')
  eventsResult = service.events().list(
        calendarId='support@sleepex.com', timeMin=now, maxResults=10, singleEvents=True,
    orderBy='startTime').execute()
  events = eventsResult.get('items', [])

  if not events:
    logging.warning('No upcoming events found.')
  else:
    finalEvent = None
    eventList = {}
    eventNum = 0
    logging.info("Logging events:")
    for event in events:
      datum = datetime.datetime.now().strftime("%Y-%m-%d")
      start = event['start'].get('dateTime', event['start'].get('date'))
      logging.info("      " + string.lower(start) + ": " + string.lower(event["summary"]))
      if(('on-call' in string.lower(event['summary']) or 'on call' in string.lower(event['summary']) or 'oncall' in string.lower(event['summary'])) and (start <= datum)):
        eventList[string.lower(event["summary"])] = [start,False]
        eventNum += 1
    finalEvent = theMainEvent(eventList,eventNum)
    logging.info(finalEvent)

    fileLoc = os.path.normpath('extensions.json')
    with open(fileLoc,'r') as extensionsFile:
      data = json.load(extensionsFile)

    if data["palloo"]["oncall"]["name"].lower() == finalEvent:
      logging.info("Google Calendar matches extension file")
    else:
      for i in data["palloo"]["extensions"]:
        if i["name"].lower() == finalEvent:
          data["palloo"]["oncall"] = i
          break
      with open(fileLoc, 'w') as extensionsFile:
        json.dump(data, extensionsFile)
      logging.info("Extensions file updated to match Google Calendar")

if __name__ == '__main__':
  main()