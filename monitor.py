import sys
import time
from watchdog.observers import Observer
from watchdog.events import FileSystemEventHandler
import os

class MonitorDeArquivos(FileSystemEventHandler):
    def __init__(self, comando, arquivos_ignorados):
        self.comando = comando
        self.arquivos_ignorados = arquivos_ignorados

    def on_modified(self, event):
        if not event.is_directory and event.src_path not in self.arquivos_ignorados:  
            print(f'.')
            print(f'Arquivo {event.src_path} foi modificado.')
            print('Executando comando:', self.comando)
            os.system(self.comando)
            print(f'.')
            print(f'.')
        else:
            print(f'.')
            print(f'Ignorando modificação em: {event.src_path}')

if __name__ == "__main__":
    caminho = './src'  # Define o caminho para o diretório atual
    comando = 'php -d phar.readonly=0 build.php'  # Define o comando a ser executado
    arquivos_ignorados = [
        'monitor.py',  # Caminho completo ou relativo para monitor.py
        './build/cvdw.phar'  # Caminho completo ou relativo para o arquivo gerado
    ]

    event_handler = MonitorDeArquivos(comando, arquivos_ignorados)
    observer = Observer()
    observer.schedule(event_handler, caminho, recursive=True)
    observer.start()

    print("Iniciando monitoramento do diretório:", caminho)
    print("O script está ativo. Pressione Ctrl+C para encerrar.")

    try:
        while True:
            time.sleep(1)
    except KeyboardInterrupt:
        print("Encerrando monitoramento...")
        observer.stop()

    observer.join()
