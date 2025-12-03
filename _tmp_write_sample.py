from openpyxl import Workbook
from pathlib import Path
path = Path("public/import_templates/students_sample.xlsx")
wb = Workbook()
ws = wb.active
ws.title = "students"
ws.append(["student_code","title","first_name","last_name","gender","room"])
rows = [
    ["10001","เด็กชาย","นักเรียนหนึ่ง","นามสกุลหนึ่ง","M","ป.1/1"],
    ["10002","เด็กหญิง","นักเรียนสอง","นามสกุลสอง","F","ป.1/2"],
    ["10003","เด็กชาย","นักเรียนสาม","นามสกุลสาม","M","ป.1/2"],
    ["10004","เด็กหญิง","นักเรียนสี่","นามสกุลสี่","F","ป.1/3"],
]
for r in rows:
    ws.append(r)
wb.save(path)
print("saved", path)
