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
 * Norwegian
 */

$.FE.LANGUAGE['nb'] = {
  translation: {
    // Place holder
    "Type something": "Skriv noe",

    // Basic formatting
    "Bold": "Fet",
    "Italic": "Kursiv",
    "Underline": "Understreket",
    "Strikethrough": "Gjennomstreket",

    // Main buttons
    "Insert": "Sett",
    "Delete": "Slett",
    "Cancel": "Avbryt",
    "OK": "OK",
    "Back": "Tilbake",
    "Remove": "Fjern",
    "More": "Mer",
    "Update": "Oppdatering",
    "Style": "Stil",

    // Font
    "Font Family": "Skriftsnitt",
    "Font Size": "St\u00f8rrelse",

    // Colors
    "Colors": "Farger",
    "Background": "Bakgrunn",
    "Text": "Tekst",
    "HEX Color": "Heksefarge",

    // Paragraphs
    "Paragraph Format": "Stiler",
    "Normal": "Normal",
    "Code": "Kode",
    "Heading 1": "Overskrift 1",
    "Heading 2": "Overskrift 2",
    "Heading 3": "Overskrift 3",
    "Heading 4": "Overskrift 4",

    // Style
    "Paragraph Style": "Avsnittsstil",
    "Inline Style": "P\u00e5 linje stil",

    // Alignment
    "Align": "Justering",
    "Align Left": "Venstrejustert",
    "Align Center": "Midtstilt",
    "Align Right": "H\u00f8yrejustert",
    "Align Justify": "Juster alle linjer",
    "None": "None",

    // Lists
    "Ordered List": "Ordnet liste",
    "Default": "Misligholde",
    "Lower Alpha": "Lavere alfa",
    "Lower Greek": "Lavere greske",
    "Lower Roman": "Lavere romersk",
    "Upper Alpha": "Øvre alfa",
    "Upper Roman": "Øvre roman",

    "Unordered List": "Uordnet liste",
    "Circle": "Sirkel",
    "Disc": "Plate",
    "Square": "Torget",

    // Line height
    "Line Height": "Linjehøyde",
    "Single": "Enkelt",
    "Double": "Dobbelt",

    // Indent
    "Decrease Indent": "Reduser innrykk",
    "Increase Indent": "\u00d8k innrykk",

    // Links
    "Insert Link": "Sett inn lenke",
    "Open in new tab": "\u00c5pne i ny fane",
    "Open Link": "\u00c5pne lenke",
    "Edit Link": "Rediger lenke",
    "Unlink": "Fjern lenke",
    "Choose Link": "Velge lenke",

    // Images
    "Insert Image": "Sett inn bilde",
    "Upload Image": "Last opp bilde",
    "By URL": "Ved URL",
    "Browse": "Bla",
    "Drop image": "Slippe bilde",
    "or click": "eller klikk",
    "Manage Images": "Bildebehandling",
    "Loading": "Lasting",
    "Deleting": "Slette",
    "Tags": "Tags",
    "Are you sure? Image will be deleted.": "Er du sikker? Bildet vil bli slettet.",
    "Replace": "Erstatte",
    "Uploading": "Opplasting",
    "Loading image": "Lasting bilde",
    "Display": "Utstilling",
    "Inline": "P\u00e5 linje",
    "Break Text": "Brudd tekst",
    "Alternative Text": "Alternativ tekst",
    "Change Size": "Endre st\u00f8rrelse",
    "Width": "Bredde",
    "Height": "H\u00f8yde",
    "Something went wrong. Please try again.": "Noe gikk galt. V\u00e6r s\u00e5 snill, pr\u00f8v p\u00e5 nytt.",
    "Image Caption": "Bilde bildetekst",
    "Advanced Edit": "Avansert redigering",

    // Video
    "Insert Video": "Sett inn video",
    "Embedded Code": "Embedded kode",
    "Paste in a video URL": "Lim inn i en video-url",
    "Drop video": "Slipp video",
    "Your browser does not support HTML5 video.": "Nettleseren din støtter ikke html5 video.",
    "Upload Video": "Last opp video",

    // Tables
    "Insert Table": "Sett inn tabell",
    "Table Header": "Tabell header",
    "Remove Table": "Fjern tabell",
    "Table Style": "Tabell stil",
    "Horizontal Align": "Horisontal justering",
    "Row": "Rad",
    "Insert row above": "Sett inn rad f\u00f8r",
    "Insert row below": "Sett in rad etter",
    "Delete row": "Slett rad",
    "Column": "Kolonne",
    "Insert column before": "Sett inn kolonne f\u00f8r",
    "Insert column after": "Sett inn kolonne etter",
    "Delete column": "Slett kolonne",
    "Cell": "Celle",
    "Merge cells": "Sl\u00e5 sammen celler",
    "Horizontal split": "Horisontalt delt",
    "Vertical split": "Vertikal split",
    "Cell Background": "Celle bakgrunn",
    "Vertical Align": "Vertikal justering",
    "Top": "Topp",
    "Middle": "Midten",
    "Bottom": "Bunn",
    "Align Top": "Justere toppen",
    "Align Middle": "Justere midten",
    "Align Bottom": "Justere bunnen",
    "Cell Style": "Celle stil",

    // Files
    "Upload File": "Opplastingsfil",
    "Drop file": "Slippe fil",

    // Emoticons
    "Emoticons": "Emoticons",
    "Grinning face": "Flirer ansikt",
    "Grinning face with smiling eyes": "Flirer ansikt med smilende \u00f8yne",
    "Face with tears of joy": "Ansikt med t\u00e5rer av glede",
    "Smiling face with open mouth": "Smilende ansikt med \u00e5pen munn",
    "Smiling face with open mouth and smiling eyes": "Smilende ansikt med \u00e5pen munn og smilende \u00f8yne",
    "Smiling face with open mouth and cold sweat": "Smilende ansikt med \u00e5pen munn og kald svette",
    "Smiling face with open mouth and tightly-closed eyes": "Smilende ansikt med \u00e5pen munn og tett lukkede \u00f8yne",
    "Smiling face with halo": "Smilende ansikt med glorie",
    "Smiling face with horns": "Smilende ansikt med horn",
    "Winking face": "Blunk ansikt",
    "Smiling face with smiling eyes": "Smilende ansikt med smilende \u00f8yne",
    "Face savoring delicious food": "M\u00f8te nyter deilig mat",
    "Relieved face": "Lettet ansikt",
    "Smiling face with heart-shaped eyes": "Smilende ansikt med hjerteformede \u00f8yne",
    "Smiling face with sunglasses": "Smilende ansikt med solbriller",
    "Smirking face": "Tilfreds ansikt",
    "Neutral face": "N\u00f8ytral ansikt",
    "Expressionless face": "Uttrykksl\u00f8st ansikt",
    "Unamused face": "Ikke moret ansikt",
    "Face with cold sweat": "Ansikt med kald svette",
    "Pensive face": "Tankefull ansikt",
    "Confused face": "Forvirret ansikt",
    "Confounded face": "Skamme ansikt",
    "Kissing face": "Kyssing ansikt",
    "Face throwing a kiss": "Ansikt kaste et kyss",
    "Kissing face with smiling eyes": "Kyssing ansikt med smilende \u00f8yne",
    "Kissing face with closed eyes": "Kyssing ansiktet med lukkede \u00f8yne",
    "Face with stuck out tongue": "Ansikt med stakk ut tungen",
    "Face with stuck out tongue and winking eye": "Ansikt med stakk ut tungen og blunke \u00f8ye",
    "Face with stuck out tongue and tightly-closed eyes": "Ansikt med fast ut tungen og tett lukket \u00f8yne",
    "Disappointed face": "Skuffet ansikt",
    "Worried face": "Bekymret ansikt",
    "Angry face": "Sint ansikt",
    "Pouting face": "Trutmunn ansikt",
    "Crying face": "Gr\u00e5ter ansikt",
    "Persevering face": "Utholdende ansikt",
    "Face with look of triumph": "Ansikt med utseendet til triumf",
    "Disappointed but relieved face": "Skuffet men lettet ansikt",
    "Frowning face with open mouth": "Rynke ansikt med \u00e5pen munn",
    "Anguished face": "Forpint ansikt",
    "Fearful face": "Engstelig ansikt",
    "Weary face": "Slitne ansiktet",
    "Sleepy face": "S\u00f8vnig ansikt",
    "Tired face": "Tr\u00f8tt ansikt",
    "Grimacing face": "Griner ansikt",
    "Loudly crying face": "H\u00f8ylytt gr\u00e5tende ansikt",
    "Face with open mouth": "Ansikt med \u00e5pen munn",
    "Hushed face": "Lavm\u00e6lt ansikt",
    "Face with open mouth and cold sweat": "Ansikt med \u00e5pen munn og kald svette",
    "Face screaming in fear": "Ansikt skriker i frykt",
    "Astonished face": "Forbauset ansikt",
    "Flushed face": "Flushed ansikt",
    "Sleeping face": "Sovende ansikt",
    "Dizzy face": "Svimmel ansikt",
    "Face without mouth": "Ansikt uten munn",
    "Face with medical mask": "Ansikt med medisinsk maske",

    // Line breaker
    "Break": "Brudd",

    // Math
    "Subscript": "Senket skrift",
    "Superscript": "Hevet skrift",

    // Full screen
    "Fullscreen": "Full skjerm",

    // Horizontal line
    "Insert Horizontal Line": "Sett inn horisontal linje",

    // Clear formatting
    "Clear Formatting": "Fjerne formatering",

    // Save
    "Save": "Lagre",

    // Undo, redo
    "Undo": "Angre",
    "Redo": "Utf\u00f8r likevel",

    // Select all
    "Select All": "Marker alt",

    // Code view
    "Code View": "Kodevisning",

    // Quote
    "Quote": "Sitat",
    "Increase": "\u00d8ke",
    "Decrease": "Nedgang",

    // Quick Insert
    "Quick Insert": "Hurtiginnsats",

    // Spcial Characters
    "Special Characters": "Spesielle karakterer",
    "Latin": "Latin",
    "Greek": "Gresk",
    "Cyrillic": "Kyrilliske",
    "Punctuation": "Tegnsetting",
    "Currency": "Valuta",
    "Arrows": "Piler",
    "Math": "Matte",
    "Misc": "Misc",

    // Print.
    "Print": "Skrive ut",

    // Spell Checker.
    "Spell Checker": "Stavekontroll",

    // Help
    "Help": "Hjelp",
    "Shortcuts": "Snarveier",
    "Inline Editor": "Inline editor",
    "Show the editor": "Vis redaktøren",
    "Common actions": "Felles handlinger",
    "Copy": "Kopiere",
    "Cut": "Kutte opp",
    "Paste": "Lim inn",
    "Basic Formatting": "Grunnleggende formatering",
    "Increase quote level": "Øke tilbudsnivået",
    "Decrease quote level": "Redusere tilbudsnivå",
    "Image / Video": "Bilde / video",
    "Resize larger": "Endre størrelsen større",
    "Resize smaller": "Endre størrelsen mindre",
    "Table": "Bord",
    "Select table cell": "Velg tabellcelle",
    "Extend selection one cell": "Utvide valg en celle",
    "Extend selection one row": "Utvide valg en rad",
    "Navigation": "Navigasjon",
    "Focus popup / toolbar": "Fokus popup / verktøylinje",
    "Return focus to previous position": "Returnere fokus til tidligere posisjon",

    // Embed.ly
    "Embed URL": "Legge inn nettadressen",
    "Paste in a URL to embed": "Lim inn i en URL for å legge inn",

    // Word Paste.
    "The pasted content is coming from a Microsoft Word document. Do you want to keep the format or clean it up?": "Det limte innholdet kommer fra et Microsoft Word-dokument. vil du beholde formatet eller rydde det opp?",
    "Keep": "Beholde",
    "Clean": "Ren",
    "Word Paste Detected": "Ordpasta oppdages"
  },
  direction: "ltr"
};

}));
