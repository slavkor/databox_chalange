set dr = %cd%

SCHTASKS /CREATE /SC DAILY /TN "Databox\facebookpush" /TR "%~dp0facebook.bat" /ST 08:00
SCHTASKS /CREATE /SC DAILY /TN "Databox\googlepush" /TR "%~dp0google.bat" /ST 08:00