@echo off
title Campus Market GitHub Auto Updater
color 0A

cd /d "C:\xampp\htdocs\Campus Market"

echo ==========================================
echo        Campus Market GitHub Updater
echo ==========================================
echo.

echo Checking Git status...
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
echo Creating commit...
git commit -m "%msg%"

echo.
echo Syncing with GitHub...
git pull --rebase origin main

if errorlevel 1 (
    color 0C
    echo.
    echo *****************************************
    echo Rebase failed! Please resolve conflicts.
    echo *****************************************
    pause
    exit /b
)

echo.
echo Pushing to GitHub...
git push origin main

if errorlevel 1 (
    color 0C
    echo.
    echo Push failed!
    pause
    exit /b
)

echo.
echo Opening GitHub Repository...
start https://github.com/abhishek027aks/Campus-Market

echo.
echo ==========================================
echo      SUCCESS! PROJECT UPDATED
echo ==========================================
color 0A
pause