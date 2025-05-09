/*!
 * froala_editor v2.9.3 (https://www.froala.com/wysiwyg-editor)
 * License https://froala.com/wysiwyg-editor/terms/
 * Copyright 2014-2019 Froala Labs
 */

(function (factory) {
    if (typeof define === 'function' && define.amd) {
        // AMD. Register as an anonymous module.
        define(['jquery'], factory);
    } else if (typeof module === 'object' && module.exports) {
        // Node/CommonJS
        module.exports = function( root, jQuery ) {
            if ( jQuery === undefined ) {
                // require('jQuery') returns a factory that requires window to
                // build a jQuery instance, we normalize how we use modules
                // that require this pattern but the window provided is a noop
                // if it's defined (how jquery works)
                if ( typeof window !== 'undefined' ) {
                    jQuery = require('jquery');
                }
                else {
                    jQuery = require('jquery')(root);
                }
            }
            return factory(jQuery);
        };
    } else {
        // Browser globals
        factory(window.jQuery);
    }
}(function ($) {
/**
 * Portuguese spoken in Portugal
 */

$.FE.LANGUAGE['pt_pt'] = {
  translation: {
    // Place holder
    "Type something": "Digite algo",

    // Basic formatting
    "Bold": "Negrito",
    "Italic": "It\u00e1lico",
    "Underline": "Sublinhado",
    "Strikethrough": "Rasurado",

    // Main buttons
    "Insert": "Inserir",
    "Delete": "Apagar",
    "Cancel": "Cancelar",
    "OK": "Ok",
    "Back": "Voltar",
    "Remove": "Remover",
    "More": "Mais",
    "Update": "Atualizar",
    "Style": "Estilo",

    // Font
    "Font Family": "Fonte",
    "Font Size": "Tamanho da fonte",

    // Colors
    "Colors": "Cores",
    "Background": "Fundo",
    "Text": "Texto",
    "HEX Color": "Cor hexadecimal",

    // Paragraphs
    "Paragraph Format": "Formatos",
    "Normal": "Normal",
    "Code": "C\u00f3digo",
    "Heading 1": "Cabe\u00e7alho 1",
    "Heading 2": "Cabe\u00e7alho 2",
    "Heading 3": "Cabe\u00e7alho 3",
    "Heading 4": "Cabe\u00e7alho 4",

    // Style
    "Paragraph Style": "Estilo de par\u00e1grafo",
    "Inline Style": "Estilo embutido",

    // Alignment
    "Align": "Alinhar",
    "Align Left": "Alinhar \u00e0 esquerda",
    "Align Center": "Alinhar ao centro",
    "Align Right": "Alinhar \u00e0 direita",
    "Align Justify": "Justificado",
    "None": "Nenhum",

    // Lists
    "Ordered List": "Lista ordenada",
    "Default": "Padrão",
    "Lower Alpha": "Alpha inferior",
    "Lower Greek": "Grego inferior",
    "Lower Roman": "Baixa romana",
    "Upper Alpha": "Alfa superior",
    "Upper Roman": "Romana superior",

    "Unordered List": "Lista n\u00e3o ordenada",
    "Circle": "Círculo",
    "Disc": "Disco",
    "Square": "Quadrado",

    // Line height
    "Line Height": "Altura da linha",
    "Single": "Solteiro",
    "Double": "Em dobro",

    // Indent
    "Decrease Indent": "Diminuir avan\u00e7o",
    "Increase Indent": "Aumentar avan\u00e7o",

    // Links
    "Insert Link": "Inserir link",
    "Open in new tab": "Abrir em uma nova aba",
    "Open Link": "Abrir link",
    "Edit Link": "Editar link",
    "Unlink": "Remover link",
    "Choose Link": "Escolha o link",

    // Images
    "Insert Image": "Inserir imagem",
    "Upload Image": "Carregar imagem",
    "By URL": "Por URL",
    "Browse": "Procurar",
    "Drop image": "Largue imagem",
    "or click": "ou clique em",
    "Manage Images": "Gerenciar as imagens",
    "Loading": "Carregando",
    "Deleting": "Excluindo",
    "Tags": "Etiquetas",
    "Are you sure? Image will be deleted.": "Voc\u00ea tem certeza? Imagem ser\u00e1 apagada.",
    "Replace": "Substituir",
    "Uploading": "Carregando imagem",
    "Loading image": "Carregando imagem",
    "Display": "Exibir",
    "Inline": "Em linha",
    "Break Text": "Texto de quebra",
    "Alternative Text": "Texto alternativo",
    "Change Size": "Alterar tamanho",
    "Width": "Largura",
    "Height": "Altura",
    "Something went wrong. Please try again.": "Algo deu errado. Por favor, tente novamente.",
    "Image Caption": "Legenda da imagem",
    "Advanced Edit": "Edição avançada",

    // Video
    "Insert Video": "Inserir v\u00eddeo",
    "Embedded Code": "C\u00f3digo embutido",
    "Paste in a video URL": "Colar em um URL de vídeo",
    "Drop video": "Solte o video",
    "Your browser does not support HTML5 video.": "Seu navegador não suporta o vídeo html5.",
    "Upload Video": "Envio vídeo",

    // Tables
    "Insert Table": "Inserir tabela",
    "Table Header": "Cabe\u00e7alho da tabela",
    "Remove Table": "Remover tabela",
    "Table Style": "estilo de tabela",
    "Horizontal Align": "Alinhamento horizontal",
    "Row": "Linha",
    "Insert row above": "Inserir linha antes",
    "Insert row below": "Inserir linha depois",
    "Delete row": "Eliminar linha",
    "Column": "Coluna",
    "Insert column before": "Inserir coluna antes",
    "Insert column after": "Inserir coluna depois",
    "Delete column": "Eliminar coluna",
    "Cell": "C\u00e9lula",
    "Merge cells": "Unir c\u00e9lulas",
    "Horizontal split": "Divis\u00e3o horizontal",
    "Vertical split": "Divis\u00e3o vertical",
    "Cell Background": "Fundo da c\u00e9lula",
    "Vertical Align": "Alinhar vertical",
    "Top": "Topo",
    "Middle": "Meio",
    "Bottom": "Fundo",
    "Align Top": "Alinhar topo",
    "Align Middle": "Alinhar meio",
    "Align Bottom": "Alinhar fundo",
    "Cell Style": "Estilo de c\u00e9lula",

    // Files
    "Upload File": "Upload de arquivo",
    "Drop file": "Largar arquivo",

    // Emoticons
    "Emoticons": "Emoticons",
    "Grinning face": "Sorrindo a cara",
    "Grinning face with smiling eyes": "Sorrindo rosto com olhos sorridentes",
    "Face with tears of joy": "Rosto com l\u00e1grimas de alegria",
    "Smiling face with open mouth": "Rosto de sorriso com a boca aberta",
    "Smiling face with open mouth and smiling eyes": "Rosto de sorriso com a boca aberta e olhos sorridentes",
    "Smiling face with open mouth and cold sweat": "Rosto de sorriso com a boca aberta e suor frio",
    "Smiling face with open mouth and tightly-closed eyes": "Rosto de sorriso com a boca aberta e os olhos bem fechados",
    "Smiling face with halo": "Rosto de sorriso com halo",
    "Smiling face with horns": "Rosto de sorriso com chifres",
    "Winking face": "Pisc a rosto",
    "Smiling face with smiling eyes": "Rosto de sorriso com olhos sorridentes",
    "Face savoring delicious food": "Rosto saboreando uma deliciosa comida",
    "Relieved face": "Rosto aliviado",
    "Smiling face with heart-shaped eyes": "Rosto de sorriso com os olhos em forma de cora\u00e7\u00e3o",
    "Smiling face with sunglasses": "Rosto de sorriso com \u00f3culos de sol",
    "Smirking face": "Rosto sorridente",
    "Neutral face": "Rosto neutra",
    "Expressionless face": "Rosto inexpressivo",
    "Unamused face": "O rosto n\u00e3o divertido",
    "Face with cold sweat": "Rosto com suor frio",
    "Pensive face": "O rosto pensativo",
    "Confused face": "Cara confusa",
    "Confounded face": "Rosto at\u00f4nito",
    "Kissing face": "Beijar Rosto",
    "Face throwing a kiss": "Rosto jogando um beijo",
    "Kissing face with smiling eyes": "Beijar rosto com olhos sorridentes",
    "Kissing face with closed eyes": "Beijando a cara com os olhos fechados",
    "Face with stuck out tongue": "Preso de cara com a l\u00edngua para fora",
    "Face with stuck out tongue and winking eye": "Rosto com estendeu a l\u00edngua e olho piscando",
    "Face with stuck out tongue and tightly-closed eyes": "Rosto com estendeu a língua e os olhos bem fechados",
    "Disappointed face": "Rosto decepcionado",
    "Worried face": "O rosto preocupado",
    "Angry face": "Rosto irritado",
    "Pouting face": "Beicinho Rosto",
    "Crying face": "Cara de choro",
    "Persevering face": "Perseverar Rosto",
    "Face with look of triumph": "Rosto com olhar de triunfo",
    "Disappointed but relieved face": "Fiquei Desapontado mas aliviado Rosto",
    "Frowning face with open mouth": "Sobrancelhas franzidas rosto com a boca aberta",
    "Anguished face": "O rosto angustiado",
    "Fearful face": "Cara com medo",
    "Weary face": "Rosto cansado",
    "Sleepy face": "Cara de sono",
    "Tired face": "Rosto cansado",
    "Grimacing face": "Fazendo caretas face",
    "Loudly crying face": "Alto chorando rosto",
    "Face with open mouth": "Enfrentar com a boca aberta",
    "Hushed face": "Flagrantes de rosto",
    "Face with open mouth and cold sweat": "Enfrentar com a boca aberta e suor frio",
    "Face screaming in fear": "Cara gritando de medo",
    "Astonished face": "Cara de surpresa",
    "Flushed face": "Rosto vermelho",
    "Sleeping face": "O rosto de sono",
    "Dizzy face": "Cara tonto",
    "Face without mouth": "Rosto sem boca",
    "Face with medical mask": "Rosto com m\u00e1scara m\u00e9dica",

    // Line breaker
    "Break": "Partir",

    // Math
    "Subscript": "Subscrito",
    "Superscript": "Sobrescrito",

    // Full screen
    "Fullscreen": "Tela cheia",

    // Horizontal line
    "Insert Horizontal Line": "Inserir linha horizontal",

    // Clear formatting
    "Clear Formatting": "Remover formata\u00e7\u00e3o",

    // Save
    "Save": "\u0053\u0061\u006c\u0076\u0065",

    // Undo, redo
    "Undo": "Anular",
    "Redo": "Restaurar",

    // Select all
    "Select All": "Seleccionar tudo",

    // Code view
    "Code View": "Exibi\u00e7\u00e3o de c\u00f3digo",

    // Quote
    "Quote": "Cita\u00e7\u00e3o",
    "Increase": "Aumentar",
    "Decrease": "Diminuir",

    // Quick Insert
    "Quick Insert": "Inser\u00e7\u00e3o r\u00e1pida",

    // Spcial Characters
    "Special Characters": "Caracteres especiais",
    "Latin": "Latino",
    "Greek": "Grego",
    "Cyrillic": "Cirílico",
    "Punctuation": "Pontuação",
    "Currency": "Moeda",
    "Arrows": "Setas; flechas",
    "Math": "Matemática",
    "Misc": "Misc",

    // Print.
    "Print": "Impressão",

    // Spell Checker.
    "Spell Checker": "Verificador ortográfico",

    // Help
    "Help": "Socorro",
    "Shortcuts": "Atalhos",
    "Inline Editor": "Editor em linha",
    "Show the editor": "Mostre o editor",
    "Common actions": "Ações comuns",
    "Copy": "Cópia de",
    "Cut": "Cortar",
    "Paste": "Colar",
    "Basic Formatting": "Formatação básica",
    "Increase quote level": "Aumentar o nível de cotação",
    "Decrease quote level": "Diminuir o nível de cotação",
    "Image / Video": "Imagem / video",
    "Resize larger": "Redimensionar maior",
    "Resize smaller": "Redimensionar menor",
    "Table": "Tabela",
    "Select table cell": "Selecione a célula da tabela",
    "Extend selection one cell": "Ampliar a seleção de uma célula",
    "Extend selection one row": "Ampliar a seleção uma linha",
    "Navigation": "Navegação",
    "Focus popup / toolbar": "Foco popup / barra de ferramentas",
    "Return focus to previous position": "Retornar o foco para a posição anterior",

    // Embed.ly
    "Embed URL": "URL de inserção",
    "Paste in a URL to embed": "Colar em url para incorporar",

    // Word Paste.
    "The pasted content is coming from a Microsoft Word document. Do you want to keep the format or clean it up?": "O conteúdo colado vem de um documento Microsoft Word. Você quer manter o formato ou limpá-lo?",
    "Keep": "Guarda",
    "Clean": "Limpar \ limpo",
    "Word Paste Detected": "Pasta de palavras detectada"
  },
  direction: "ltr"
};

}));
