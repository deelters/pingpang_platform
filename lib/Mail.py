import smtplib
from email.mime.text import MIMEText


class Email:#类开头要大写

    def __init__(self, email_from, password, port):#email_from:发送的email地址，password：发送email的授权码，port：端口
        self.email_from = email_from
        self.password = password
        self.port = port

    def send(self, email_to, mes_subject,mes_content,mes_from,mes_to):
        msg_from = self.email_from  # 发送方邮箱
        passwd = self.password  # 填入发送方邮箱的授权码
        msg_to = email_to  # 收件人邮箱

        subject = mes_subject  # 主题
        content = mes_content  # 正文
        msg = MIMEText(content, _subtype='html', _charset='utf-8')
        msg['Subject'] = mes_subject
        msg['From'] = mes_from
        msg['To'] = mes_to
        try:
            s = smtplib.SMTP_SSL("smtp.qq.com", self.port)  # 邮件服务器及端口号
            s.login(msg_from, passwd)
            s.sendmail(msg_from, msg_to, msg.as_string())
            print
            "发送成功"
        except s.SMTPException as e:
            print
            "发送失败"
        finally:
            s.quit()