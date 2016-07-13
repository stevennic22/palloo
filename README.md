# palloo
> A series of scripts to check current On-Call user in Halloo, set a new user, or to alert a user.

### Features

- Updates OnCall User and Emergency OnCall users from user input or Google Calendar
- Updates phone availablility for single user in phone pool
- Sends alerts to user based on input (User can default to OnCall or be specified by input)
- Swaps number calls go to for single user

### Languages

- PHP
- Python

### Requirements

| Language        | Module
| --------------- | ------------------------------------------------------------------------------------------------------
| **Python**      | [httplib2](https://pypi.python.org/pypi/httplib2)                                                     
| **Python**      | [Google API Python Client](https://developers.google.com/api-client-library/python/start/installation)

### Resources

-	[Google Calendar API Quickstart Guide](https://developers.google.com/google-apps/calendar/quickstart/python)
- [Google Calendar API Calendar Documentation](https://developers.google.com/resources/api-libraries/documentation/calendar/v3/python/latest/calendar_v3.calendarList.html#list)
- [Google Calendar API Events Documentation](https://developers.google.com/resources/api-libraries/documentation/calendar/v3/python/latest/calendar_v3.events.html#list)
