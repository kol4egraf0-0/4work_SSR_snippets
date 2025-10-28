import os
import sys

from PyQt5 import QtWidgets
from bs4 import BeautifulSoup
from transliterate import translit
import re


class HtmlEditor(QtWidgets.QWidget):
    def __init__(self):
        super().__init__()
        self.html_path = None
        self.soup = None
        self.init_ui()

    def init_ui(self):
        self.setWindowTitle("HTML/PHP Editor")

        layout = QtWidgets.QVBoxLayout()

        self.open_btn = QtWidgets.QPushButton("Открыть HTML/PHP")
        self.open_btn.clicked.connect(self.open_file)
        layout.addWidget(self.open_btn)

        self.class_input = QtWidgets.QLineEdit()
        self.class_input.setPlaceholderText('Введите полный div (например: <div class="container">)')
        layout.addWidget(self.class_input)

        self.text_edit = QtWidgets.QTextEdit()
        self.text_edit.setPlaceholderText(
            "Введите текст - автоформатирование в HTML:\n"
            "• Строки с '-', '·' → <ul>\n" 
            "• Строки с '1.', '2.' → <ol>\n"
            "• 2Заголовок → <h2>, 3Заголовок → <h3>, 4Заголовок → <h4>\n"
            "• Остальное → <p> (без переносов строк)"
        )
        layout.addWidget(self.text_edit)

        self.microdata_checkbox = QtWidgets.QCheckBox("Добавлять микроразметку")
        layout.addWidget(self.microdata_checkbox)

        self.save_name_input = QtWidgets.QLineEdit()
        self.save_name_input.setPlaceholderText("Введите имя файла на русском (например, запчасти)")
        layout.addWidget(self.save_name_input)

        self.save_btn = QtWidgets.QPushButton("Сохранить HTML")
        self.save_btn.clicked.connect(self.save_file)
        layout.addWidget(self.save_btn)

        self.setLayout(layout)

    def open_file(self):
        path, _ = QtWidgets.QFileDialog.getOpenFileName(self, "Открыть HTML/PHP", "", 
                                                       "Web files (*.html *.htm *.php)")
        if path:
            self.html_path = path
            with open(path, "r", encoding="utf-8") as f:
                content = f.read()
                self.original_content = content
                self.soup = BeautifulSoup(content, 'html.parser')
            QtWidgets.QMessageBox.information(self, "Файл открыт", f"Файл {os.path.basename(path)} загружен")

    def extract_class_from_html(self, full_div_html):
        try:
            input_soup = BeautifulSoup(full_div_html, 'html.parser')
            input_div = input_soup.find('div')
            
            if not input_div:
                return None
            
            class_attr = input_div.get('class', [])
            if class_attr:
                if isinstance(class_attr, list):
                    return class_attr
                return [class_attr]
            return None
            
        except Exception as e:
            print(f"Ошибка извлечения класса: {e}")
            return None

    def find_exact_div(self, target_classes):
        if not target_classes:
            return None
        
        all_divs = self.soup.find_all('div', class_=lambda x: x and any(cls in x for cls in target_classes))
        
        for div in all_divs:
            div_classes = div.get('class', [])
            if isinstance(div_classes, str):
                div_classes = [div_classes]
            
            if set(div_classes) == set(target_classes):
                return div
        
        return None

    def add_microdata_to_element(self, element, content):
        if element.name == 'h1':
            element['itemprop'] = 'name'
        elif element.name == 'p':
            element['itemprop'] = 'description'
        
        if element.string:
            element.string = content
            meta = self.soup.new_tag('meta')
            meta['content'] = content
            meta['itemprop'] = 'name' if element.name == 'h1' else 'text'
            element.insert_after(meta)

    def auto_format_text_to_html(self, text):
        lines = text.strip().split('\n')
        html_parts = []
        current_list = []
        list_type = None
        current_paragraph = []
        
        i = 0
        while i < len(lines):
            line = lines[i].strip()
            
            if not line:
                i += 1
                continue
            
            header_match = re.match(r'^(\d)(.+)$', line)
            if header_match:
                header_level = int(header_match.group(1))
                header_text = header_match.group(2).strip()
                
                if current_paragraph:
                    html_parts.append(f'<p>{" ".join(current_paragraph)}</p>')
                    current_paragraph = []
                
                if current_list:
                    if list_type == 'ul':
                        html_parts.append('<ul>' + ''.join(f'<li>{item}</li>' for item in current_list) + '</ul>')
                    else:
                        html_parts.append('<ol>' + ''.join(f'<li>{item}</li>' for item in current_list) + '</ol>')
                    current_list = []
                    list_type = None
                
                if 2 <= header_level <= 6:
                    html_parts.append(f'<h{header_level}>{header_text}</h{header_level}>')
                else:
                    html_parts.append(f'<h2>{header_text}</h2>')
                
                i += 1
                continue
            
            if re.match(r'^[·•\-]\s+', line) or re.match(r'^\d+\.\s+', line):
                if current_paragraph:
                    html_parts.append(f'<p>{" ".join(current_paragraph)}</p>')
                    current_paragraph = []
                
                if re.match(r'^\d+\.\s+', line):
                    new_list_type = 'ol'
                    line_content = re.sub(r'^\d+\.\s+', '', line)
                else:
                    new_list_type = 'ul'
                    line_content = re.sub(r'^[·•\-]\s+', '', line)
                
                if new_list_type != list_type or not current_list:
                    if current_list:
                        if list_type == 'ul':
                            html_parts.append('<ul>' + ''.join(f'<li>{item}</li>' for item in current_list) + '</ul>')
                        else:
                            html_parts.append('<ol>' + ''.join(f'<li>{item}</li>' for item in current_list) + '</ol>')
                        current_list = []
                    
                    list_type = new_list_type
                
                current_list.append(line_content)
                
            else:
                if current_list:
                    if list_type == 'ul':
                        html_parts.append('<ul>' + ''.join(f'<li>{item}</li>' for item in current_list) + '</ul>')
                    else:
                        html_parts.append('<ol>' + ''.join(f'<li>{item}</li>' for item in current_list) + '</ol>')
                    current_list = []
                    list_type = None
                
                if (len(line) < 100 and 
                    (line.endswith(':') or 
                     any(word in line.lower() for word in ['этапы', 'почему', 'преимущества', 'виды']) or
                     line.isupper() or
                     (sum(1 for c in line if c.isupper()) > len(line) * 0.3))):
                    
                    if current_paragraph:
                        html_parts.append(f'<p>{" ".join(current_paragraph)}</p>')
                        current_paragraph = []
                    
                    html_parts.append(f'<h2>{line}</h2>')
                else:
                    current_paragraph.append(line)
            
            i += 1
        
        if current_paragraph:
            html_parts.append(f'<p>{" ".join(current_paragraph)}</p>')
        
        if current_list:
            if list_type == 'ul':
                html_parts.append('<ul>' + ''.join(f'<li>{item}</li>' for item in current_list) + '</ul>')
            else:
                html_parts.append('<ol>' + ''.join(f'<li>{item}</li>' for item in current_list) + '</ol>')
        
        return '\n'.join(html_parts)

    def save_file(self):
        if not self.soup:
            QtWidgets.QMessageBox.warning(self, "Ошибка", "Сначала откройте HTML/PHP")
            return

        full_div_html = self.class_input.text().strip()
        plain_text = self.text_edit.toPlainText().strip()
        rus_name = self.save_name_input.text().strip()

        if not full_div_html or not plain_text or not rus_name:
            QtWidgets.QMessageBox.warning(self, "Ошибка", "Заполните все поля")
            return

        formatted_html = self.auto_format_text_to_html(plain_text)
        print("Сгенерированный HTML:")
        print(formatted_html)

        target_classes = self.extract_class_from_html(full_div_html)
        if not target_classes:
            QtWidgets.QMessageBox.warning(self, "Ошибка", "Не удалось извлечь класс из div")
            return

        div = self.find_exact_div(target_classes)
        if not div:
            similar_divs = self.soup.find_all('div', class_=lambda x: x and any(cls in x for cls in target_classes))
            if similar_divs:
                classes_found = [div.get('class', []) for div in similar_divs]
                QtWidgets.QMessageBox.warning(self, "Ошибка", 
                    f"Div с точным классом '{target_classes}' не найден.\n"
                    f"Найдены div с классами: {classes_found}")
            else:
                QtWidgets.QMessageBox.warning(self, "Ошибка", 
                    f"Div с классом '{target_classes}' не найден")
            return

        div.clear()
        
        try:
            new_content_soup = BeautifulSoup(formatted_html, 'html.parser')
            
            if self.microdata_checkbox.isChecked():
                for element in new_content_soup.find_all(['h1', 'p']):
                    if element.string:
                        self.add_microdata_to_element(element, element.string)
            
            div.append(new_content_soup)
        except Exception as e:
            print(f"Ошибка парсинга HTML: {e}")
            div.append(plain_text)

        eng_name = translit(rus_name, "ru", reversed=True)
        eng_name = re.sub(r'[\s_]+', '-', eng_name)
        filename = eng_name + ".html"

        save_path, _ = QtWidgets.QFileDialog.getSaveFileName(self, "Сохранить HTML", filename, 
                                                            "HTML files (*.html *.htm)")
        if save_path:
            pretty_html = self.soup.prettify()
            with open(save_path, "w", encoding="utf-8") as f:
                f.write(pretty_html)
            QtWidgets.QMessageBox.information(self, "Успех", f"Файл сохранен как {os.path.basename(save_path)}")


if __name__ == "__main__":
    app = QtWidgets.QApplication(sys.argv)
    editor = HtmlEditor()
    editor.resize(600, 400)
    editor.show()
    sys.exit(app.exec_())