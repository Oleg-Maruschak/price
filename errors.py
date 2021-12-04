import datetime


class Err:
    def __init__(self, name_file_log, err, err_sys=False):
        data = datetime.datetime.now()
        with open(name_file_log, 'a') as name:
            name.write(f'-------------------{data}----------------\n{err_sys}\n{err}\n')
