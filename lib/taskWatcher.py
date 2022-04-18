"""
乒乓球挑战赛平台 - 超时比赛结果自动上报处理程序

每隔1分钟进行比赛情况判断
"""
import time
import requests
import json


#程序主入口
if __name__ == '__main__':

    print("乒乓球挑战赛平台 - 超时比赛结果自动上报处理程序\n")
    while True:
        now_time_str = time.strftime("%Y-%m-%d %H:%M:%S", time.localtime())
        data = requests.get('http://pingpang.deelter.com/api/submitOvertimeTask')
        response_data = json.loads(data.content)
        if response_data['status'] == 'error':
            print('ERROR %s  %s' % (now_time_str, response_data['message']))
        time.sleep(60)