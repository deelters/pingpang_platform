#后台内部API接口HTTP服务端
#仅支持本地回路IP使用JSON的HTTP请求格式内容进行上报
from http.server import HTTPServer, BaseHTTPRequestHandler
import json
import threading
from Sender import Sender

data = {'result': 'this is a local API server.'}
host = ('localhost', 7899)

class Resquest(BaseHTTPRequestHandler):
    def do_POST(self):
        #仅允许POST请求下的JSON内容格式
        if self.headers['content-type'] != 'application/json':
            self.send_response(403)
            self.send_header('Content-type', 'application/json')
            self.end_headers()
            self.wfile.write(json.dumps({'message':'Method not support.'}).encode())
            return

        req_datas = self.rfile.read(int(self.headers['content-length']))
        # print(req_datas.decode())
        self.doAction(req_datas.decode())
        self.send_response(200)
        self.send_header('Content-type', 'application/json')
        self.end_headers()
        self.wfile.write(json.dumps(data).encode())

    def doAction(self, request_content):
        request_data = json.loads(request_content)
        if 'action' not in request_data:
            print("no func")
            return
        #发送邮件操作
        if request_data['action'] == 'send_mail':
            sendMail(request_data['email_address'], request_data['title'], request_data['content'])

#异步执行装饰器
def thd(a):

    def wrapper(*args,**kwargs):
        thd=threading.Thread(target=a,args=args,kwargs=kwargs)
        thd.start()
    return wrapper

#发送邮件
@thd
def sendMail(email_address, title, content):
    sender = Sender()
    sender.sendMailTo(email_address, title, content)

if __name__ == '__main__':
    server = HTTPServer(host, Resquest)
    print("Starting server, listen at: %s:%s" % host)
    server.serve_forever()