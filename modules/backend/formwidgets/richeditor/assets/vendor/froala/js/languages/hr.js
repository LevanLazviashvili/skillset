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
 * Croatian
 */

$.FE.LANGUAGE['hr'] = {
  translation: {
    // Place holder
    "Type something": "Napi\u0161i ne\u0161to",

    // Basic formatting
    "Bold": "Podebljaj",
    "Italic": "Kurziv",
    "Underline": "Podcrtano",
    "Strikethrough": "Precrtano",

    // Main buttons
    "Insert": "Umetni",
    "Delete": "Obri\u0161i",
    "Cancel": "Otka\u017ei",
    "OK": "U redu",
    "Back": "Natrag",
    "Remove": "Ukloni",
    "More": "Vi\u0161e",
    "Update": "A\u017euriraj",
    "Style": "Stil",

    // Font
    "Font Family": "Odaberi font",
    "Font Size": "Veli\u010dina fonta",

    // Colors
    "Colors": "Boje",
    "Background": "Pozadina",
    "Text": "Tekst",
    "HEX Color": "Heksadecimalne boje",

    // Paragraphs
    "Paragraph Format": "Format odlomka",
    "Normal": "Normalno",
    "Code": "Izvorni kod",
    "Heading 1": "Naslov 1",
    "Heading 2": "Naslov 2",
    "Heading 3": "Naslov 3",
    "Heading 4": "Naslov 4",

    // Style
    "Paragraph Style": "Stil odlomka",
    "Inline Style": "Stil u liniji",

    // Alignment
    "Align": "Poravnaj",
    "Align Left": "Poravnaj lijevo",
    "Align Center": "Poravnaj po sredini",
    "Align Right": "Poravnaj desno",
    "Align Justify": "Obostrano poravnanje",
    "None": "Nijedan",

    // Lists
    "Ordered List": "Ure\u0111ena lista",
    "Default": "Zadano",
    "Lower Alpha": "Niži alfa",
    "Lower Greek": "Donji grčki",
    "Lower Roman": "Niži rimski",
    "Upper Alpha": "Gornja alfa",
    "Upper Roman": "Gornji rimski",

    "Unordered List": "Neure\u0111ena lista",
    "Circle": "Krug",
    "Disc": "Disk",
    "Square": "Kvadrat",

    // Line height
    "Line Height": "Visina crte",
    "Single": "Singl",
    "Double": "Dvostruko",

    // Indent
    "Decrease Indent": "Uvuci odlomak",
    "Increase Indent": "Izvuci odlomak",

    // Links
    "Insert Link": "Umetni link",
    "Open in new tab": "Otvori u novom prozoru",
    "Open Link": "Otvori link",
    "Edit Link": "Uredi link",
    "Unlink": "Ukloni link",
    "Choose Link": "Odaberi link",

    // Images
    "Insert Image": "Umetni sliku",
    "Upload Image": "Prijenos slike",
    "By URL": "Prema URL",
    "Browse": "Odabir",
    "Drop image": "Ispusti sliku",
    "or click": "ili odaberi",
    "Manage Images": "Upravljanje slikama",
    "Loading": "U\u010ditavanje",
    "Deleting": "Brisanje",
    "Tags": "Oznake",
    "Are you sure? Image will be deleted.": "Da li ste sigurni da \u017eelite obrisati ovu sliku?",
    "Replace": "Zamijeni",
    "Uploading": "Prijenos",
    "Loading image": "Otvaram sliku",
    "Display": "Prika\u017ei",
    "Inline": "U liniji",
    "Break Text": "Odvojeni tekst",
    "Alternative Text": "Alternativni tekst",
    "Change Size": "Promjena veli\u010dine",
    "Width": "\u0160irina",
    "Height": "Visina",
    "Something went wrong. Please try again.": "Ne\u0161to je po\u0161lo po zlu. Molimo poku\u0161ajte ponovno.",
    "Image Caption": "Opis slike",
    "Advanced Edit": "Napredno uređivanje",

    // Video
    "Insert Video": "Umetni video",
    "Embedded Code": "Ugra\u0111eni kod",
    "Paste in a video URL": "Zalijepite u URL videozapisa",
    "Drop video": "Ispusti video",
    "Your browser does not support HTML5 video.": "Vaš preglednik ne podržava HTML video.",
    "Upload Video": "Prenesi videozapis",

    // Tables
    "Insert Table": "Umetni tablicu",
    "Table Header": "Zaglavlje tablice",
    "Remove Table": "Izbri\u0161i tablicu",
    "Table Style": "Tablica stil",
    "Horizontal Align": "Horizontalna poravnanje",
    "Row": "Red",
    "Insert row above": "Umetni red iznad",
    "Insert row below": "Umetni red ispod",
    "Delete row": "Obri\u0161i red",
    "Column": "Stupac",
    "Insert column before": "Umetni stupac prije",
    "Insert column after": "Umetni stupac poslije",
    "Delete column": "Obri\u0161i stupac",
    "Cell": "Polje",
    "Merge cells": "Spoji polja",
    "Horizontal split": "Horizontalno razdvajanje polja",
    "Vertical split": "Vertikalno razdvajanje polja",
    "Cell Background": "Polje pozadine",
    "Vertical Align": "Vertikalno poravnanje",
    "Top": "Vrh",
    "Middle": "Sredina",
    "Bottom": "Dno",
    "Align Top": "Poravnaj na vrh",
    "Align Middle": "Poravnaj po sredini",
    "Align Bottom": "Poravnaj na dno",
    "Cell Style": "Stil polja",

    // Files
    "Upload File": "Prijenos datoteke",
    "Drop file": "Ispusti datoteku",

    // Emoticons
    "Emoticons": "Emotikoni",
    "Grinning face": "Nacereno lice",
    "Grinning face with smiling eyes": "Nacereno lice s nasmije\u0161enim o\u010dima",
    "Face with tears of joy": "Lice sa suzama radosnicama",
    "Smiling face with open mouth": "Nasmijano lice s otvorenim ustima",
    "Smiling face with open mouth and smiling eyes": "Nasmijano lice s otvorenim ustima i nasmijanim o\u010dima",
    "Smiling face with open mouth and cold sweat": "Nasmijano lice s otvorenim ustima i hladnim znojem",
    "Smiling face with open mouth and tightly-closed eyes": "Nasmijano lice s otvorenim ustima i \u010dvrsto zatvorenih o\u010diju",
    "Smiling face with halo": "Nasmijano lice sa aureolom",
    "Smiling face with horns": "Nasmijano lice s rogovima",
    "Winking face": "Lice koje namiguje",
    "Smiling face with smiling eyes": "Nasmijano lice s nasmiješenim o\u010dima",
    "Face savoring delicious food": "Lice koje u\u017eiva ukusnu hranu",
    "Relieved face": "Lice s olak\u0161anjem",
    "Smiling face with heart-shaped eyes": "Nasmijano lice sa o\u010dima u obliku srca",
    "Smiling face with sunglasses": "Nasmijano lice sa sun\u010danim nao\u010dalama",
    "Smirking face": "Zlokobno nasmije\u0161eno lice",
    "Neutral face": "Neutralno lice",
    "Expressionless face": "Bezizra\u017eajno lice",
    "Unamused face": "Nezainteresirano lice",
    "Face with cold sweat": "Lice s hladnim znojem",
    "Pensive face": "Zami\u0161ljeno lice",
    "Confused face": "Zbunjeno lice",
    "Confounded face": "Zbunjeno lice",
    "Kissing face": "Lice s poljupcem",
    "Face throwing a kiss": "Lice koje baca poljubac",
    "Kissing face with smiling eyes": "Lice s poljupcem s nasmije\u0161enim o\u010dima",
    "Kissing face with closed eyes": "Lice s poljupcem zatvorenih o\u010diju",
    "Face with stuck out tongue": "Lice s ispru\u017eenim jezikom",
    "Face with stuck out tongue and winking eye": "Lice s ispru\u017eenim jezikom koje namiguje",
    "Face with stuck out tongue and tightly-closed eyes": "Lice s ispru\u017eenim jezikom i \u010dvrsto zatvorenih o\u010diju",
    "Disappointed face": "Razo\u010darano lice",
    "Worried face": "Zabrinuto lice",
    "Angry face": "Ljutito lice",
    "Pouting face": "Nadureno lice",
    "Crying face": "Uplakano lice",
    "Persevering face": "Lice s negodovanjem",
    "Face with look of triumph": "Trijumfalno lice",
    "Disappointed but relieved face": "Razo\u010darano ali olakšano lice",
    "Frowning face with open mouth": "Namrgo\u0111eno lice s otvorenim ustima",
    "Anguished face": "Tjeskobno lice",
    "Fearful face": "Prestra\u0161eno lice",
    "Weary face": "Umorno lice",
    "Sleepy face": "Pospano lice",
    "Tired face": "Umorno lice",
    "Grimacing face": "Lice sa grimasama",
    "Loudly crying face": "Glasno pla\u010du\u0107e lice",
    "Face with open mouth": "Lice s otvorenim ustima",
    "Hushed face": "Tiho lice",
    "Face with open mouth and cold sweat": "Lice s otvorenim ustima i hladnim znojem",
    "Face screaming in fear": "Lice koje vri\u0161ti u strahu",
    "Astonished face": "Zaprepa\u0161teno lice",
    "Flushed face": "Zajapureno lice",
    "Sleeping face": "Spava\u0107e lice",
    "Dizzy face": "Lice sa vrtoglavicom",
    "Face without mouth": "Lice bez usta",
    "Face with medical mask": "Lice s medicinskom maskom",

    // Line breaker
    "Break": "Odvojeno",

    // Math
    "Subscript": "Indeks",
    "Superscript": "Eksponent",

    // Full screen
    "Fullscreen": "Puni zaslon",

    // Horizontal line
    "Insert Horizontal Line": "Umetni liniju",

    // Clear formatting
    "Clear Formatting": "Ukloni oblikovanje",

    // Save
    "Save": "\u0055\u0161\u0074\u0065\u0064\u006a\u0065\u0074\u0069",

    // Undo, redo
    "Undo": "Korak natrag",
    "Redo": "Korak naprijed",

    // Select all
    "Select All": "Odaberi sve",

    // Code view
    "Code View": "Pregled koda",

    // Quote
    "Quote": "Citat",
    "Increase": "Pove\u0107aj",
    "Decrease": "Smanji",

    // Quick Insert
    "Quick Insert": "Brzo umetak",

    // Spcial Characters
    "Special Characters": "Posebni znakovi",
    "Latin": "Latinski",
    "Greek": "Grčki",
    "Cyrillic": "Ćirilica",
    "Punctuation": "Interpunkcija",
    "Currency": "Valuta",
    "Arrows": "Strelice",
    "Math": "Matematika",
    "Misc": "Razno",

    // Print.
    "Print": "Otisak",

    // Spell Checker.
    "Spell Checker": "Provjeritelj pravopisa",

    // Help
    "Help": "Pomoć",
    "Shortcuts": "Prečaci",
    "Inline Editor": "Inline editor",
    "Show the editor": "Prikaži urednika",
    "Common actions": "Zajedničke radnje",
    "Copy": "Kopirati",
    "Cut": "Rez",
    "Paste": "Zalijepiti",
    "Basic Formatting": "Osnovno oblikovanje",
    "Increase quote level": "Povećati razinu citata",
    "Decrease quote level": "Smanjite razinu citata",
    "Image / Video": "Slika / video",
    "Resize larger": "Promijenite veličinu većeg",
    "Resize smaller": "Promijenite veličinu manju",
    "Table": "Stol",
    "Select table cell": "Odaberite stolnu ćeliju",
    "Extend selection one cell": "Proširiti odabir jedne ćelije",
    "Extend selection one row": "Proširite odabir jednog retka",
    "Navigation": "Navigacija",
    "Focus popup / toolbar": "Fokus popup / alatnoj traci",
    "Return focus to previous position": "Vratiti fokus na prethodnu poziciju",

    // Embed.ly
    "Embed URL": "Uredi url",
    "Paste in a URL to embed": "Zalijepite URL da biste ga ugradili",

    // Word Paste.
    "The pasted content is coming from a Microsoft Word document. Do you want to keep the format or clean it up?": "Zalijepi sadržaj dolazi iz Microsoft Word dokumenta. Želite li zadržati format ili očistiti?",
    "Keep": "Zadržati",
    "Clean": "Čist",
    "Word Paste Detected": "Otkrivena je zastavica riječi"
  },
  direction: "ltr"
};

}));
