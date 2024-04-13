@echo off
setlocal EnableDelayedExpansion
del "guiNConfig.json"  "guiNDB.db" 
curl  -O  https://raw.githubusercontent.com/zhanggithubcom/subscription.github.io/main/v2ray-sub/guiNConfig.json
curl  -O  https://raw.githubusercontent.com/zhanggithubcom/subscription.github.io/main/v2ray-sub/guiNDB.db


endlocal
echo 请按下任意键退出本窗口...
pause > nul

rem 退出脚本
exit /b	