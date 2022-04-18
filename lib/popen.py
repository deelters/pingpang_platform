import os
import sys
from subprocess import Popen

if __name__ == '__main__':
    #判断参数是否正确
    if len(sys.argv) < 4:
        exit()

    os.chdir(os.getcwd() + "\..\lib")
    try:
        Popen('python Sender.py "%s" "%s" "%s"'%(sys.argv[1], sys.argv[2], sys.argv[3]))
    except Exception  as e:
        fp = open("error.txt", 'w')
        fp.write(repr(e) + '\n')
        fp.close()