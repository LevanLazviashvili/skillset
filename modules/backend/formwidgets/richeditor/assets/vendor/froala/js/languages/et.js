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
 * Estonian
 */

$.FE.LANGUAGE['et'] = {
  translation: {
    // Place holder
    "Type something": "Kirjuta midagi",

    // Basic formatting
    "Bold": "Rasvane",
    "Italic": "Kursiiv",
    "Underline": "Allajoonitud",
    "Strikethrough": "L\u00e4bikriipsutatud",

    // Main buttons
    "Insert": "Lisa",
    "Delete": "Kustuta",
    "Cancel": "T\u00fchista",
    "OK": "OK",
    "Back": "Tagasi",
    "Remove": "Eemaldama",
    "More": "Rohkem",
    "Update": "Ajakohastama",
    "Style": "Stiil",

    // Font
    "Font Family": "Fondi perekond",
    "Font Size": "Fondi suurus",

    // Colors
    "Colors": "V\u00e4rvid",
    "Background": "Taust",
    "Text": "Tekst",
    "HEX Color": "Hex värvi",

    // Paragraphs
    "Paragraph Format": "Paragrahv formaat",
    "Normal": "Normaalne",
    "Code": "Kood",
    "Heading 1": "P\u00e4is 1",
    "Heading 2": "P\u00e4is 2",
    "Heading 3": "P\u00e4is 3",
    "Heading 4": "P\u00e4is 4",

    // Style
    "Paragraph Style": "Paragrahv stiil",
    "Inline Style": "J\u00e4rjekorras stiil",

    // Alignment
    "Align": "Joonda",
    "Align Left": "Joonda vasakule",
    "Align Center": "Joonda keskele",
    "Align Right": "Joonda paremale",
    "Align Justify": "R\u00f6\u00f6pjoondus",
    "None": "Mitte \u00fckski",

    // Lists
    "Ordered List": "Tellitud nimekirja",
    "Default": "Vaikimisi",
    "Lower Alpha": "Alumine alfa",
    "Lower Greek": "Alumine kreeklane",
    "Lower Roman": "Madalam roomlane",
    "Upper Alpha": "Ülemine alfa",
    "Upper Roman": "Ülemine rooma",

    "Unordered List": "Tavalise nimekirja",
    "Circle": "Ringi",
    "Disc": "Plaat",
    "Square": "Ruut",

    // Line height
    "Line Height": "Reakõrgus",
    "Single": "Üksik",
    "Double": "Topelt",

    // Indent
    "Decrease Indent": "V\u00e4henemine taane",
    "Increase Indent": "Suurenda taanet",

    // Links
    "Insert Link": "Lisa link",
    "Open in new tab": "Ava uues sakis",
    "Open Link": "Avatud link",
    "Edit Link": "Muuda link",
    "Unlink": "Eemalda link",
    "Choose Link": "Vali link",

    // Images
    "Insert Image": "Lisa pilt",
    "Upload Image": "Laadige pilt",
    "By URL": "Poolt URL",
    "Browse": "sirvida",
    "Drop image": "Aseta pilt",
    "or click": "v\u00f5i kliki",
    "Manage Images": "Halda pilte",
    "Loading": "Laadimine",
    "Deleting": "Kustutamine",
    "Tags": "Sildid",
    "Are you sure? Image will be deleted.": "Oled sa kindel? Pilt kustutatakse.",
    "Replace": "Asendama",
    "Uploading": "Laadimise pilti",
    "Loading image": "Laadimise pilti",
    "Display": "Kuvama",
    "Inline": "J\u00e4rjekorras",
    "Break Text": "Murdma teksti",
    "Alternative Text": "Asendusliikme teksti",
    "Change Size": "Muuda suurust",
    "Width": "Laius",
    "Height": "K\u00f5rgus",
    "Something went wrong. Please try again.": "Midagi l\u00e4ks valesti. Palun proovi uuesti.",
    "Image Caption": "Pildi pealkiri",
    "Advanced Edit": "Täiustatud redigeerimine",

    // Video
    "Insert Video": "Lisa video",
    "Embedded Code": "Varjatud koodi",
    "Paste in a video URL": "Kleebi video URL-i",
    "Drop video": "Tilk videot",
    "Your browser does not support HTML5 video.": "Teie brauser ei toeta html5-videot.",
    "Upload Video": "Video üleslaadimine",

    // Tables
    "Insert Table": "Sisesta tabel",
    "Table Header": "Tabel p\u00e4ise kaudu",
    "Remove Table": "Eemalda tabel",
    "Table Style": "Tabel stiili",
    "Horizontal Align": "Horisontaalne joonda",
    "Row": "Rida",
    "Insert row above": "Sisesta rida \u00fcles",
    "Insert row below": "Sisesta rida alla",
    "Delete row": "Kustuta rida",
    "Column": "Veerg",
    "Insert column before": "Sisesta veerg ette",
    "Insert column after": "Sisesta veerg j\u00e4rele",
    "Delete column": "Kustuta veerg",
    "Cell": "Lahter",
    "Merge cells": "\u00fchenda lahtrid",
    "Horizontal split": "Poolita horisontaalselt",
    "Vertical split": "Poolita vertikaalselt",
    "Cell Background": "Lahter tausta",
    "Vertical Align": "Vertikaalne joonda",
    "Top": "\u00fclemine",
    "Middle": "Keskmine",
    "Bottom": "P\u00f5hi",
    "Align Top": "Joonda \u00fclemine",
    "Align Middle": "Joonda keskmine",
    "Align Bottom": "Joonda P\u00f5hi",
    "Cell Style": "Lahter stiili",

    // Files
    "Upload File": "Lae fail \u00fcles",
    "Drop file": "Aseta fail",

    // Emoticons
    "Emoticons": "Emotikonid",
    "Grinning face": "Irvitas n\u00e4kku",
    "Grinning face with smiling eyes": "Irvitas n\u00e4kku naeratavad silmad",
    "Face with tears of joy": "N\u00e4gu r\u00f5\u00f5mupisaratega",
    "Smiling face with open mouth": "Naeratav n\u00e4gu avatud suuga",
    "Smiling face with open mouth and smiling eyes": "Naeratav n\u00e4gu avatud suu ja naeratavad silmad",
    "Smiling face with open mouth and cold sweat": "Naeratav n\u00e4gu avatud suu ja k\u00fclm higi",
    "Smiling face with open mouth and tightly-closed eyes": "Naeratav n\u00e4gu avatud suu ja tihedalt suletud silmad",
    "Smiling face with halo": "Naeratav n\u00e4gu halo",
    "Smiling face with horns": "Naeratav n\u00e4gu sarved",
    "Winking face": "Pilgutab n\u00e4gu",
    "Smiling face with smiling eyes": "Naeratav n\u00e4gu naeratab silmad",
    "Face savoring delicious food": "N\u00e4gu nautides maitsvat toitu",
    "Relieved face": "P\u00e4\u00e4stetud n\u00e4gu",
    "Smiling face with heart-shaped eyes": "Naeratav n\u00e4gu s\u00fcdajas silmad",
    "Smiling face with sunglasses": "Naeratav n\u00e4gu p\u00e4ikeseprillid",
    "Smirking face": "Muigama n\u00e4gu ",
    "Neutral face": "Neutraalne n\u00e4gu",
    "Expressionless face": "Ilmetu n\u00e4gu",
    "Unamused face": "Morn n\u00e4gu",
    "Face with cold sweat": "N\u00e4gu k\u00fclma higiga",
    "Pensive face": "M\u00f5tlik n\u00e4gu",
    "Confused face": "Segaduses n\u00e4gu",
    "Confounded face": "Segas n\u00e4gu",
    "Kissing face": "Suudlevad n\u00e4gu",
    "Face throwing a kiss": "N\u00e4gu viskamine suudlus",
    "Kissing face with smiling eyes": "Suudlevad n\u00e4gu naeratab silmad",
    "Kissing face with closed eyes": "Suudlevad n\u00e4gu, silmad kinni",
    "Face with stuck out tongue": "N\u00e4gu ummikus v\u00e4lja keele",
    "Face with stuck out tongue and winking eye": "N\u00e4gu ummikus v\u00e4lja keele ja silma pilgutav silma",
    "Face with stuck out tongue and tightly-closed eyes": "N\u00e4gu ummikus v\u00e4lja keele ja silmad tihedalt suletuna",
    "Disappointed face": "Pettunud n\u00e4gu",
    "Worried face": "Mures n\u00e4gu",
    "Angry face": "Vihane n\u00e4gu",
    "Pouting face": "Tursik n\u00e4gu",
    "Crying face": "Nutt n\u00e4gu",
    "Persevering face": "Püsiv n\u00e4gu",
    "Face with look of triumph": "N\u00e4gu ilme triumf",
    "Disappointed but relieved face": "Pettunud kuid vabastati n\u00e4gu",
    "Frowning face with open mouth": "Kulmukortsutav n\u00e4gu avatud suuga",
    "Anguished face": "Ahastavad n\u00e4gu",
    "Fearful face": "Hirmunult n\u00e4gu",
    "Weary face": "Grimasse",
    "Sleepy face": "Unine n\u00e4gu",
    "Tired face": "V\u00e4sinud n\u00e4gu",
    "Grimacing face": "Grimassitavaks n\u00e4gu",
    "Loudly crying face": "Valjusti nutma n\u00e4gu",
    "Face with open mouth": "N\u00e4gu avatud suuga",
    "Hushed face": "Raskel n\u00e4gu",
    "Face with open mouth and cold sweat": "N\u00e4gu avatud suu ja k\u00fclm higi",
    "Face screaming in fear": "N\u00e4gu karjuvad hirm",
    "Astonished face": "Lummatud n\u00e4gu",
    "Flushed face": "Punetav n\u00e4gu",
    "Sleeping face": "Uinuv n\u00e4gu",
    "Dizzy face": "Uimane n\u00fcgu",
    "Face without mouth": "N\u00e4gu ilma suu",
    "Face with medical mask": "N\u00e4gu meditsiinilise mask",

    // Line breaker
    "Break": "Murdma",

    // Math
    "Subscript": "Allindeks",
    "Superscript": "\u00dclaindeks",

    // Full screen
    "Fullscreen": "T\u00e4isekraanil",

    // Horizontal line
    "Insert Horizontal Line": "Sisesta horisontaalne joon",

    // Clear formatting
    "Clear Formatting": "Eemalda formaatimine",

    // Save
    "Save": "Salvesta",

    // Undo, redo
    "Undo": "V\u00f5ta tagasi",
    "Redo": "Tee uuesti",

    // Select all
    "Select All": "Vali k\u00f5ik",

    // Code view
    "Code View": "Koodi vaadata",

    // Quote
    "Quote": "Tsitaat",
    "Increase": "Suurendama",
    "Decrease": "V\u00e4henda",

    // Quick Insert
    "Quick Insert": "Kiire sisestada",

    // Spcial Characters
    "Special Characters": "Erimärgid",
    "Latin": "Latin",
    "Greek": "Kreeka keel",
    "Cyrillic": "Kirillitsa",
    "Punctuation": "Kirjavahemärgid",
    "Currency": "Valuuta",
    "Arrows": "Nooled",
    "Math": "Matemaatika",
    "Misc": "Misc",

    // Print.
    "Print": "Printige",

    // Spell Checker.
    "Spell Checker": "Õigekirja kontrollija",

    // Help
    "Help": "Abi",
    "Shortcuts": "Otseteed",
    "Inline Editor": "Sisemine redaktor",
    "Show the editor": "Näita redaktorit",
    "Common actions": "Ühised meetmed",
    "Copy": "Koopia",
    "Cut": "Lõigake",
    "Paste": "Kleepige",
    "Basic Formatting": "Põhiline vormindamine",
    "Increase quote level": "Suurendada tsiteerimise taset",
    "Decrease quote level": "Langetada tsiteerimise tase",
    "Image / Video": "Pilt / video",
    "Resize larger": "Suuruse muutmine suurem",
    "Resize smaller": "Väiksema suuruse muutmine",
    "Table": "Laud",
    "Select table cell": "Vali tabeli lahtrisse",
    "Extend selection one cell": "Laiendage valikut üks lahtrisse",
    "Extend selection one row": "Laiendage valikut ühe reana",
    "Navigation": "Navigeerimine",
    "Focus popup / toolbar": "Fookuse hüpikakna / tööriistariba",
    "Return focus to previous position": "Tagasi pöörata tähelepanu eelmisele positsioonile",

    // Embed.ly
    "Embed URL": "Embed url",
    "Paste in a URL to embed": "Kleepige URL-i sisestamiseks",

    // Word Paste.
    "The pasted content is coming from a Microsoft Word document. Do you want to keep the format or clean it up?": "Kleepitud sisu pärineb Microsoft Wordi dokumendist. kas soovite vormi säilitada või puhastada?",
    "Keep": "Pidage seda",
    "Clean": "Puhas",
    "Word Paste Detected": "Avastatud sõna pasta"
  },
  direction: "ltr"
};

}));
