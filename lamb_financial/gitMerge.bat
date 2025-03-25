@echo off

for /f "tokens=2 delims==" %%G in ('wmic os get localdatetime /value') do set datetime=%%G

set year=%datetime:~0,4%
set month=%datetime:~4,2%
set day=%datetime:~6,2%
set hour=%datetime:~8,2%
set minute=%datetime:~10,2%
set second=%datetime:~12,2%

git status
git add .
git status
set /p message="Ingresa el mensaje del commit: "
git commit -m "%day%/%month%/%year% %hour%:%minute%:%second% -> %message%"
git pull
git pull origin develop
