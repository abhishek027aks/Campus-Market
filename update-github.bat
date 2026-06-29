@echo off
title Campus Market - GitHub Auto Update

cd /d "C:\xampp\htdocs\Campus Market"

echo ==========================================
echo        Campus Market GitHub Updater
echo ==========================================
echo.

git status

echo.
echo Adding all files...
git add .

echo.
set /p msg=Enter Commit Message: 

if "%msg%"=="" (
    set msg=Project Update
)

echo.
echo Committing...
git commit -m "%msg%"

echo.
echo Pushing to GitHub...
git push origin main

echo.
echo ==========================================
echo          Update Completed!
echo ==========================================
pause