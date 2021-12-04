import pandas as pd

def getTable(urlFiles):
    formatFile = urlFiles[urlFiles.rfind('.') + 1:]
    if formatFile == 'csv':
        try:
            data = pd.read_csv(urlFiles, delimiter=';', encoding='cp1251')
        except UnicodeDecodeError:
            data = pd.read_csv(urlFiles, delimiter=';')
        return data
    elif formatFile == 'xlsx' or formatFile == 'xls':
        data = pd.read_excel(urlFiles)
        return data

def getUserAgent(agent, name):
    user = {name: []}
    file = open(agent, 'r')
    readFile = file.read()
    list = readFile.split("\n")
    for i in list:
        user[name].append(i)
    data = pd.DataFrame(user)
    file.close()
    return data

def save_log(err, name):
    log = open(f'log{name}.txt', 'a')
    log.write(err)
    log.close()

def get_result(data, folder):
    frame = pd.DataFrame(data)
    frame = frame.rename(columns={frame.columns[-1]: 'doc'})
    frame = frame.rename(columns={frame.columns[-2]: 'exist'})
    frame.to_excel(folder, index=False)