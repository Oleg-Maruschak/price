import PySimpleGUI as sg
import config
import function
import threading
import time as t
from model import Connect_db
import EXISTClass
import DOKClass
import random
from errors import Err


def get_result(class_, article, agent, ip, ccc, column, window, selfip, PRICECART):
    len_article = len(article)
    ip_list = ip
    i = 0
    agent_ = {'accept': config.ACCEPT, 'user-agent': ''}
    while i < len_article:
        if len(ip_list) > 0:
            agent_['user-agent'] = random.choice(agent)[1]
            ips = random.choice(ip_list)
            rowid_ip = ips[0]
            ipss = {'http': f'http://{ips[1]}', 'https': f'https://{ips[1]}'}
        else:
            if selfip:
                ipss = {}
            else:
                window['-ERROR-'].update('Нет активных proxy!!!!', text_color='red')
                break
        result = class_.get_article(article[i], agent_, ipss, PRICECART)
        if result == 'ip':
            ccc.update_ip(rowid_ip)
            ip_list.remove(ips)
            Err('ip-error.txt', ips[1], column)
        elif result == 'err':
            window['-ERROR-'].update(f'Произошла ошибка {column} !!!', text_color='red')
            break
        elif result == 'timeout' or result == 'html':
            i += 1
        else:
            ccc.update_article(article[i][0], result, column)
            i += 1
        if column == 'dok':
            window['-PROGDOK-'].update(int(i)/int(len_article)*100)
        else:
            window['-PROGEXIST-'].update(int(i) / int(len_article) * 100)
        t.sleep(random.randint(2, 10))
    window.write_event_value('-THREAD DONE-', column)


def go(class_, article, agent, ip, ccc, column, window, selfip, PRICECART=False):
    thread_id = threading.Thread(
        target=get_result,
        args=(class_, article, agent, ip, ccc, column, window, selfip, PRICECART),
        daemon=True)
    thread_id.start()

#####################################################################################################################
#####################################################################################################################
#####################################################################################################################
sg.theme('Light Brown 3')

col1 = sg.Column([
    # Information sg.Frame
    [sg.Frame('Ресурс:', [[sg.Checkbox('exist.ua', default=True, key='-EXIST-', size=(10, 1)),
                           sg.Checkbox('dok.ua', default=True, key='-DOK-', size=(10, 1))]], )],
    [sg.Frame('Обновить proxy:', [[sg.InputText(key='-PROXY-', size=(36, 20)), sg.FileBrowse(), sg.Button('Submit', key='-SUBMITPROXY-'), sg.Text(key='-TEXTPROXY-', size=(2))]], )],
    [sg.Frame('Обновить user_agent:', [[sg.InputText(key='-USERAGENT-', size=(36, 20)), sg.FileBrowse(), sg.Button('Submit', key='-SUBMITUSERAGENT-'), sg.Text(key='-TEXTUSERAGENT-', size=(2))]], )],
    [sg.Frame('Обновить список артикулов:', [[sg.InputText(key='-ARTICLEFORPRSING-', size=(36, 20)), sg.FileBrowse(), sg.Button('Submit', key='-SUBMITARTICLEUPDATE-'), sg.Text(key='-TEXTARTICLE-', size=(2))]], )],
    [sg.Frame('Progress-EXIST:', [[sg.ProgressBar(100, orientation='h', size=(29, 20), key='-PROGEXIST-', bar_color=('green', 'azure2')), sg.Text(key='-TEXTEXIST-', size=(2))]], )],
    [sg.Frame('Progress-DOK:', [[sg.ProgressBar(100, orientation='h', size=(29,20), key='-PROGDOK-', bar_color=('green', 'azure2')), sg.Text(key='-TEXTDOK-', size=(2))]], )]], vertical_alignment='top')

col3 = sg.Column([[sg.Frame('', [[sg.Column([[sg.Button('GO', size=(50, 1), button_color='olive'), ]], size=(426, 45), pad=(0, 0))],
                                 [sg.Column([[sg.Button('Выгрузить результат в Excel:', size=(50, 1), key='-SAVEFILE-'), ]], size=(426, 45), pad=(0, 0))],
                                 [sg.Frame('Error:', [[sg.Text('', key='-ERROR-', size=(49, 1))]], )]])]], pad=(0, 0))
col2 = [sg.Checkbox('Разрешить использовать свой IP', key='-SELFIP-')]
col4 = [sg.Checkbox('Цена из карточки товара', key='-PRICECART-')]
# The final layout is a simple one
layout = [[col1], [col2], [col3, col4]]

# A perhaps better layout would have been to use the vtop layout helpful function.
# This would allow the col2 column to have a different height and still be top aligned
# layout = [sg.vtop([col1, col2]),
#           [col3]]


window = sg.Window('price for APS', layout, icon=b'iVBORw0KGgoAAAANSUhEUgAAAFUAAABPBAMAAABolpSUAAAABGdBTUEAALGPC/xhBQAAAAFzUkdCAK7OHOkAAAAtUExURQBFed4THP///++NkcDS3v/ZAedSWPbHyYCkvD52m//sgICafP/1wD5ygMC/Xhrf/tkAAAAJcEhZcwAAEnQAABJ0Ad5mH3gAAAKxSURBVEjH5Ze9ittAEMeX4wofRwJ7gqS1V0a6gzSSimvNcSnSBtSYNHLh1KeA3Brjwu5EuEDcXpk+L5B3uBcI5D1udmb1satdyUcIgWQKI5ufx/+Z2ZkdM3G0jdl/xmacX/w9NtLY4GMP6/O4zUb01sr6xITAejk8cM5zFxsBI3x4lbbMA67r1liJJLy2bMCvYa8cejdSr2Gv7ezVyOJ4YWdLthEThSwz9eBZ2SvGRiTCu5bvC8JjG7tmjP2UIhL1QXhnOq7YKaDnc3CcGGkBx4nBggT2PX0QnnGGwHHEDRYknKfpB/qN3f6wVTXHisQaKyU8pmn6A54vmbSzG3i8o2xcaKyU8AvYGiU41DKn2DFKSN83KMGqOHmbBblfSELDslOl2PBbyiyk8yrRyjbk2NPyIEMDtw9KztluX0oWToisul4LCO0FRSZFjNQvMUYiYpN9qSQIsaMsliSiw84wtLnWtZckImr6qGYfMWNtW6OISSe22/1bFZrueCMCk5X25puwsX7TdH1zEkScYJnbbJhIg3a41azE0gF7vVo1bNWzzLQRDQ2SMcxmdnb6DPZ4v95vsqcdVqxWBbKQpXstbdAanzCh7Vr4yGL2B+8sH8szY3R4+9kQO3CMR2vwLsQzLY/L52GWzrRs9aNYCo7d0wc3bpYaFqcDwusTNxtQE2IFDttdaUlIO2dS8EybJFbWz1SVsdXr+lpZNTxjmphq9rn8NsPz3ZAGwWvHFbxxxhbR9YujYHoov257ajHhsd+5KHNHzuLmjqrvLM+9w/iteU+yevYduh1oycGVIxZ9Oww21zJJlt2r25hnnZ0gd7PZkftD3ftt85xsoIOZa3+oTlBeVGsRFse5cxU4YMKiWk7kF5yxFbQ4ZSqovh0R4EWbDeKjdtrFM/bfP8L+K/9FngDiqbl9Tp5ilwAAAABJRU5ErkJggg==')

try:
    db = Connect_db()
except Exception:
    window['-ERROR-'].update('Нет подключения к db !!!', text_color='red')

while True:
    event, values = window.read()
    if event == sg.WIN_CLOSED:
        break
    if event == '-SUBMITPROXY-':
        window['-ERROR-'].update('')
        try:
            proxy = function.getUserAgent(values['-PROXY-'], 'ip')
            db.insert_for_parsing(proxy, 'ip')
            window['-TEXTPROXY-'].update('OK', text_color='green')
        except Exception:
            window['-ERROR-'].update('Ошибка обновления proxy !!!', text_color='red')
    if event == '-SUBMITUSERAGENT-':
        window['-ERROR-'].update('')
        try:
            user_agent = function.getUserAgent(values['-USERAGENT-'], 'user_agent')
            db.insert_for_parsing(user_agent, 'user_agent')
            window['-TEXTUSERAGENT-'].update('OK', text_color='green')
        except Exception:
            window['-ERROR-'].update('Ошибка обновления user agent !!!', text_color='red')
    if event == '-SUBMITARTICLEUPDATE-':
        window['-ERROR-'].update('')
        try:
            articleForParsing = function.getTable(values['-ARTICLEFORPRSING-'])
            db.insert_for_parsing(articleForParsing)
            window['-TEXTARTICLE-'].update('OK', text_color='green')
        except Exception:
            window['-ERROR-'].update('Ошибка обновления артикулов !!!', text_color='red')
    if event == 'GO':
        window['-PROGEXIST-'].update(0)
        window['-PROGDOK-'].update(0)
        window['-TEXTEXIST-'].update('')
        window['-TEXTDOK-'].update('')
        window['-ERROR-'].update('')
        exist = EXISTClass.EXIST()
        doc = DOKClass.DOK()
        article = db.select_search_article()
        if len(article) == 0:
            window['-ERROR-'].update('Нет позиций для мониторинга !!!', text_color='red')
            continue
        agent = db.select_agent()
        ip = db.select_ip()
        if values['-EXIST-'] == False and values['-DOK-'] == False:
            window['-ERROR-'].update('Не выбран ресурс!!!', text_color='red')
        elif values['-EXIST-'] and values['-DOK-']:
            go(exist, article, agent, ip, db, 'exist', window, values['-SELFIP-'], values['-PRICECART-'])
            t.sleep(2)
            go(doc, article, agent, ip, db, 'dok', window, values['-SELFIP-'])
        elif values['-EXIST-'] and values['-DOK-'] == False:
            go(exist, article, agent, ip, db, 'exist', window, values['-SELFIP-'], values['-PRICECART-'])
        elif values['-EXIST-'] == False and values['-DOK-']:
            go(doc, article, agent, ip, db, 'dok', window, values['-SELFIP-'])

    if event == '-SAVEFILE-':
        result = db.select_result()
        folder = sg.popup_get_folder('Папка для сохранения:')
        if folder == None or folder == '':
            window['-ERROR-'].update('Не выбрали папку для сохранения !!!', text_color='red')
        else:
            window['-ERROR-'].update('')
            function.get_result(result, f'{folder}{config.NAME_FILE_EXCEL}')
            sg.popup('Файл выгружен: ', folder)

    elif event == '-THREAD DONE-':
        if values['-THREAD DONE-'] == 'exist':
            window['-TEXTEXIST-'].update('OK', text_color='green')
        else:
            window['-TEXTDOK-'].update('OK', text_color='green')

db.closes()
window.close()