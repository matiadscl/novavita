#!/usr/bin/env python3
"""Convert Markdown files to styled PDF using weasyprint."""
import base64
import markdown
from weasyprint import HTML

LOGO_PATH = '/home/coder/facand/logofacand.png'

# Read and encode logo as base64
with open(LOGO_PATH, 'rb') as f:
    logo_b64 = base64.b64encode(f.read()).decode()

CSS = """
@page {
    size: A4;
    margin: 2cm 1.8cm;
    @bottom-center { content: counter(page); font-size: 9px; color: #888; }
}
body {
    font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
    font-size: 11px;
    line-height: 1.5;
    color: #1a1a1a;
}
h1 { font-size: 22px; color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 6px; margin-top: 0; }
h2 { font-size: 16px; color: #2c3e50; border-bottom: 1px solid #ddd; padding-bottom: 4px; margin-top: 20px; }
h3 { font-size: 13px; color: #34495e; margin-top: 16px; }
h4 { font-size: 11px; color: #555; }
table { width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 9.5px; }
th { background: #2c3e50; color: white; padding: 6px 8px; text-align: left; font-weight: 600; }
td { padding: 5px 8px; border-bottom: 1px solid #e0e0e0; }
tr:nth-child(even) td { background: #f8f9fa; }
strong { color: #2c3e50; }
hr { border: none; border-top: 1px solid #ddd; margin: 16px 0; }
blockquote { border-left: 3px solid #3498db; margin: 10px 0; padding: 6px 12px; background: #f0f7ff; font-style: italic; }
ul, ol { padding-left: 20px; }
li { margin-bottom: 3px; }
code { background: #f4f4f4; padding: 1px 4px; border-radius: 3px; font-size: 10px; }
.header-banner {
    background: linear-gradient(135deg, #2c3e50, #3498db);
    color: white; padding: 20px 24px; margin: -2cm -1.8cm 20px -1.8cm;
    text-align: center;
}
.header-banner img.logo { height: 40px; margin-bottom: 8px; }
.header-banner h1 { color: white; border: none; font-size: 20px; margin: 0; }
.header-banner p { color: #ccc; margin: 4px 0 0 0; font-size: 10px; }
"""

def convert(md_path, pdf_path, title, subtitle):
    with open(md_path, 'r') as f:
        md_text = f.read()

    # Remove the first few header lines (we'll use the banner instead)
    lines = md_text.split('\n')
    start = 0
    for i, line in enumerate(lines):
        if line.startswith('---') and i > 0:
            start = i + 1
            break
    if start > 0:
        md_text = '\n'.join(lines[start:])

    # Remove markdown image tags (logo is handled in banner)
    import re
    md_text = re.sub(r'!\[.*?\]\(.*?\)\s*\n?', '', md_text)

    html_body = markdown.markdown(md_text, extensions=['tables', 'fenced_code'])

    html = f"""<!DOCTYPE html>
<html><head><meta charset="utf-8"><style>{CSS}</style></head>
<body>
<div class="header-banner">
    <img class="logo" src="data:image/png;base64,{logo_b64}" alt="Facand">
    <h1>{title}</h1>
    <p>{subtitle}</p>
</div>
{html_body}
</body></html>"""

    HTML(string=html).write_pdf(pdf_path)
    print(f"OK: {pdf_path}")

if __name__ == '__main__':
    base = '/home/coder/clientes/novavita/docs'

    convert(
        f'{base}/analisis_competencia_productos_precios_17abril.md',
        f'{base}/analisis_competencia_productos_precios_17abril.pdf',
        'Análisis de Competencia: Productos, Precios y Propuesta de Valor',
        'Novavita Clínica & Spa — Curicó | 17 abril 2026 | Vale (Facand)'
    )

    convert(
        f'{base}/analisis_competencia_instagram_17abril.md',
        f'{base}/analisis_competencia_instagram_17abril.pdf',
        'Análisis de Competencia: Instagram',
        'Novavita Clínica & Spa — Curicó | 17 abril 2026 | Vale (Facand)'
    )
