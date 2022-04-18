from Mail import Email
import sys

class Sender:
    __username = '1827901085@qq.com'
    __password = 'xgciyjqzsqgidhhj'

    def __init__(self):
        self.__mail = Email(self.__username, self.__password, 465)

    def sendMailTo(self, receiver_address, title, content):
        self.__mail.send(receiver_address, title, content, self.__username, receiver_address)

if __name__ == '__main__':
    #判断参数是否正确
    if len(sys.argv) < 4:
        exit()

    mail_sender = Sender()
    mail_sender.sendMailTo(sys.argv[1], sys.argv[2], sys.argv[3])