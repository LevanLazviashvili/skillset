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
 * Arabic
 */

$.FE.LANGUAGE['ar'] = {
  translation: {
    // Place holder
    "Type something": "\u0627\u0643\u062a\u0628 \u0634\u064a\u0626\u0627",

    // Basic formatting
    "Bold": "\u063a\u0627\u0645\u0642",
    "Italic": "\u0645\u0627\u0626\u0644",
    "Underline": "\u062a\u0633\u0637\u064a\u0631",
    "Strikethrough": "\u064a\u062a\u0648\u0633\u0637 \u062e\u0637",

    // Main buttons
    "Insert": "\u0625\u062f\u0631\u0627\u062c",
    "Delete": "\u062d\u0630\u0641",
    "Cancel": "\u0625\u0644\u063a\u0627\u0621",
    "OK": "\u0645\u0648\u0627\u0641\u0642",
    "Back": "\u0638\u0647\u0631",
    "Remove": "\u0625\u0632\u0627\u0644\u0629",
    "More": "\u0623\u0643\u062b\u0631",
    "Update": "\u0627\u0644\u062a\u062d\u062f\u064a\u062b",
    "Style": "\u0623\u0633\u0644\u0648\u0628",

    // Font
    "Font Family": "\u0639\u0627\u0626\u0644\u0629 \u0627\u0644\u062e\u0637",
    "Font Size": "\u062d\u062c\u0645 \u0627\u0644\u062e\u0637",

    // Colors
    "Colors": "\u0627\u0644\u0623\u0644\u0648\u0627\u0646",
    "Background": "\u0627\u0644\u062e\u0644\u0641\u064a\u0629",
    "Text": "\u0627\u0644\u0646\u0635",
    "HEX Color": "عرافة اللون",

    // Paragraphs
    "Paragraph Format": "\u062a\u0646\u0633\u064a\u0642 \u0627\u0644\u0641\u0642\u0631\u0629",
    "Normal": "\u0637\u0628\u064a\u0639\u064a",
    "Code": "\u0643\u0648\u062f",
    "Heading 1": "\u0627\u0644\u0639\u0646\u0627\u0648\u064a\u0646 1",
    "Heading 2": "\u0627\u0644\u0639\u0646\u0627\u0648\u064a\u0646 2",
    "Heading 3": "\u0627\u0644\u0639\u0646\u0627\u0648\u064a\u0646 3",
    "Heading 4": "\u0627\u0644\u0639\u0646\u0627\u0648\u064a\u0646 4",

    // Style
    "Paragraph Style": "\u0646\u0645\u0637 \u0627\u0644\u0641\u0642\u0631\u0629",
    "Inline Style": "\u0627\u0644\u0646\u0645\u0637 \u0627\u0644\u0645\u0636\u0645\u0646",

    // Alignment
    "Align": "\u0645\u062d\u0627\u0630\u0627\u0629",
    "Align Left": "\u0645\u062d\u0627\u0630\u0627\u0629 \u0627\u0644\u0646\u0635 \u0644\u0644\u064a\u0633\u0627\u0631",
    "Align Center": "\u062a\u0648\u0633\u064a\u0637",
    "Align Right": "\u0645\u062d\u0627\u0630\u0627\u0629 \u0627\u0644\u0646\u0635 \u0644\u0644\u064a\u0645\u064a\u0646",
    "Align Justify": "\u0636\u0628\u0637",
    "None": "\u0644\u0627 \u0634\u064a\u0621",

    // Lists
    "Ordered List": "\u0642\u0627\u0626\u0645\u0629 \u0645\u0631\u062a\u0628\u0629",
    "Default": "الافتراضي",
    "Lower Alpha": "أقل ألفا",
    "Lower Greek": "أقل اليونانية",
    "Lower Roman": "انخفاض الروماني",
    "Upper Alpha": "العلوي ألفا",
    "Upper Roman": "الروماني العلوي",

    "Unordered List": "\u0642\u0627\u0626\u0645\u0629 \u063a\u064a\u0631 \u0645\u0631\u062a\u0628\u0629",
    "Circle": "دائرة",
    "Disc": "القرص",
    "Square": "ميدان",

    // Line height
    "Line Height": "ارتفاع خط",
    "Single": "غير مرتبطة",
    "Double": "مزدوج",

    // Indent
    "Decrease Indent": "\u0627\u0646\u062e\u0641\u0627\u0636 \u0627\u0644\u0645\u0633\u0627\u0641\u0629 \u0627\u0644\u0628\u0627\u062f\u0626\u0629",
    "Increase Indent": "\u0632\u064a\u0627\u062f\u0629 \u0627\u0644\u0645\u0633\u0627\u0641\u0629 \u0627\u0644\u0628\u0627\u062f\u0626\u0629",

    // Links
    "Insert Link": "\u0625\u062f\u0631\u0627\u062c \u0631\u0627\u0628\u0637",
    "Open in new tab": "\u0641\u062a\u062d \u0641\u064a \u0639\u0644\u0627\u0645\u0629 \u062a\u0628\u0648\u064a\u0628 \u062c\u062f\u064a\u062f\u0629",
    "Open Link": "\u0627\u0641\u062a\u062d \u0627\u0644\u0631\u0627\u0628\u0637",
    "Edit Link": "\u0627\u0631\u062a\u0628\u0627\u0637 \u062a\u062d\u0631\u064a\u0631",
    "Unlink": "\u062d\u0630\u0641 \u0627\u0644\u0631\u0627\u0628\u0637",
    "Choose Link": "\u0627\u062e\u062a\u064a\u0627\u0631 \u0635\u0644\u0629",

    // Images
    "Insert Image": "\u0625\u062f\u0631\u0627\u062c \u0635\u0648\u0631\u0629",
    "Upload Image": "\u062a\u062d\u0645\u064a\u0644 \u0635\u0648\u0631\u0629",
    "By URL": "\u0628\u0648\u0627\u0633\u0637\u0629 URL",
    "Browse": "\u062a\u0635\u0641\u062d",
    "Drop image": "\u0625\u0633\u0642\u0627\u0637 \u0635\u0648\u0631\u0629",
    "or click": "\u0623\u0648 \u0627\u0646\u0642\u0631 \u0641\u0648\u0642",
    "Manage Images": "\u0625\u062f\u0627\u0631\u0629 \u0627\u0644\u0635\u0648\u0631",
    "Loading": "\u062a\u062d\u0645\u064a\u0644",
    "Deleting": "\u062d\u0630\u0641",
    "Tags": "\u0627\u0644\u0643\u0644\u0645\u0627\u062a",
    "Are you sure? Image will be deleted.": "\u0647\u0644 \u0623\u0646\u062a \u0645\u062a\u0623\u0643\u062f\u061f \u0633\u064a\u062a\u0645 \u062d\u0630\u0641 \u0627\u0644\u0635\u0648\u0631\u0629\u002e",
    "Replace": "\u0627\u0633\u062a\u0628\u062f\u0627\u0644",
    "Uploading": "\u062a\u062d\u0645\u064a\u0644",
    "Loading image": "\u0635\u0648\u0631\u0629 \u062a\u062d\u0645\u064a\u0644",
    "Display": "\u0639\u0631\u0636",
    "Inline": "\u0641\u064a \u062e\u0637",
    "Break Text": "\u0646\u0635 \u0627\u0633\u062a\u0631\u0627\u062d\u0629",
    "Alternative Text": "\u0646\u0635 \u0628\u062f\u064a\u0644",
    "Change Size": "\u062a\u063a\u064a\u064a\u0631 \u062d\u062c\u0645",
    "Width": "\u0639\u0631\u0636",
    "Height": "\u0627\u0631\u062a\u0641\u0627\u0639",
    "Something went wrong. Please try again.": ".\u062d\u062f\u062b \u062e\u0637\u0623 \u0645\u0627. \u062d\u0627\u0648\u0644 \u0645\u0631\u0629 \u0627\u062e\u0631\u0649",
    "Image Caption": "تعليق على الصورة",
    "Advanced Edit": "تعديل متقدم",

    // Video
    "Insert Video": "\u0625\u062f\u0631\u0627\u062c \u0641\u064a\u062f\u064a\u0648",
    "Embedded Code": "\u0627\u0644\u062a\u0639\u0644\u064a\u0645\u0627\u062a \u0627\u0644\u0628\u0631\u0645\u062c\u064a\u0629 \u0627\u0644\u0645\u0636\u0645\u0646\u0629",
    "Paste in a video URL": "لصق في عنوان ورل للفيديو",
    "Drop video": "انخفاض الفيديو",
    "Your browser does not support HTML5 video.": "متصفحك لا يدعم فيديو HTML5.",
    "Upload Video": "رفع فيديو",

    // Tables
    "Insert Table": "\u0625\u062f\u0631\u0627\u062c \u062c\u062f\u0648\u0644",
    "Table Header": "\u0631\u0623\u0633 \u0627\u0644\u062c\u062f\u0648\u0644",
    "Remove Table": "\u0625\u0632\u0627\u0644\u0629 \u0627\u0644\u062c\u062f\u0648\u0644",
    "Table Style": "\u0646\u0645\u0637 \u0627\u0644\u062c\u062f\u0648\u0644",
    "Horizontal Align": "\u0645\u062d\u0627\u0630\u0627\u0629 \u0623\u0641\u0642\u064a\u0629",
    "Row": "\u0635\u0641",
    "Insert row above": "\u0625\u062f\u0631\u0627\u062c \u0635\u0641 \u0644\u0644\u0623\u0639\u0644\u0649",
    "Insert row below": "\u0625\u062f\u0631\u0627\u062c \u0635\u0641 \u0644\u0644\u0623\u0633\u0641\u0644",
    "Delete row": "\u062d\u0630\u0641 \u0635\u0641",
    "Column": "\u0639\u0645\u0648\u062f",
    "Insert column before": "\u0625\u062f\u0631\u0627\u062c \u0639\u0645\u0648\u062f \u0644\u0644\u064a\u0633\u0627\u0631",
    "Insert column after": "\u0625\u062f\u0631\u0627\u062c \u0639\u0645\u0648\u062f \u0644\u0644\u064a\u0645\u064a\u0646",
    "Delete column": "\u062d\u0630\u0641 \u0639\u0645\u0648\u062f",
    "Cell": "\u062e\u0644\u064a\u0629",
    "Merge cells": "\u062f\u0645\u062c \u062e\u0644\u0627\u064a\u0627",
    "Horizontal split": "\u0627\u0646\u0642\u0633\u0627\u0645 \u0623\u0641\u0642\u064a",
    "Vertical split": "\u0627\u0644\u0627\u0646\u0642\u0633\u0627\u0645 \u0627\u0644\u0639\u0645\u0648\u062f\u064a",
    "Cell Background": "\u062e\u0644\u0641\u064a\u0629 \u0627\u0644\u062e\u0644\u064a\u0629",
    "Vertical Align": "\u0645\u062d\u0627\u0630\u0627\u0629 \u0639\u0645\u0648\u062f\u064a\u0629",
    "Top": "\u0623\u0639\u0644\u0649",
    "Middle": "\u0648\u0633\u0637",
    "Bottom": "\u0623\u0633\u0641\u0644",
    "Align Top": "\u0645\u062d\u0627\u0630\u0627\u0629 \u0623\u0639\u0644\u0649",
    "Align Middle": "\u0645\u062d\u0627\u0630\u0627\u0629 \u0648\u0633\u0637",
    "Align Bottom": "\u0645\u062d\u0627\u0630\u0627\u0629 \u0627\u0644\u0623\u0633\u0641\u0644",
    "Cell Style": "\u0646\u0645\u0637 \u0627\u0644\u062e\u0644\u064a\u0629",

    // Files
    "Upload File": "\u062a\u062d\u0645\u064a\u0644 \u0627\u0644\u0645\u0644\u0641",
    "Drop file": "\u0627\u0646\u062e\u0641\u0627\u0636 \u0627\u0644\u0645\u0644\u0641",

    // Emoticons
    "Emoticons": "\u0627\u0644\u0645\u0634\u0627\u0639\u0631",
    "Grinning face": "\u064a\u0643\u0634\u0631 \u0648\u062c\u0647\u0647",
    "Grinning face with smiling eyes": "\u0645\u0628\u062a\u0633\u0645\u0627 \u0648\u062c\u0647 \u0645\u0639 \u064a\u0628\u062a\u0633\u0645 \u0627\u0644\u0639\u064a\u0646",
    "Face with tears of joy": "\u0648\u062c\u0647 \u0645\u0639 \u062f\u0645\u0648\u0639 \u0627\u0644\u0641\u0631\u062d",
    "Smiling face with open mouth": "\u0627\u0644\u0648\u062c\u0647 \u0627\u0644\u0645\u0628\u062a\u0633\u0645 \u0645\u0639 \u0641\u062a\u062d \u0627\u0644\u0641\u0645",
    "Smiling face with open mouth and smiling eyes": "\u0627\u0644\u0648\u062c\u0647 \u0627\u0644\u0645\u0628\u062a\u0633\u0645 \u0645\u0639 \u0641\u062a\u062d \u0627\u0644\u0641\u0645 \u0648\u0627\u0644\u0639\u064a\u0646\u064a\u0646 \u064a\u0628\u062a\u0633\u0645",
    "Smiling face with open mouth and cold sweat": "\u0627\u0644\u0648\u062c\u0647 \u0627\u0644\u0645\u0628\u062a\u0633\u0645 \u0645\u0639 \u0641\u062a\u062d \u0627\u0644\u0641\u0645 \u0648\u0627\u0644\u0639\u0631\u0642 \u0627\u0644\u0628\u0627\u0631\u062f",
    "Smiling face with open mouth and tightly-closed eyes": "\u0627\u0644\u0648\u062c\u0647 \u0627\u0644\u0645\u0628\u062a\u0633\u0645 \u0645\u0639 \u0641\u062a\u062d \u0627\u0644\u0641\u0645 \u0648\u0627\u0644\u0639\u064a\u0646\u064a\u0646 \u0645\u063a\u0644\u0642\u0629 \u0628\u0625\u062d\u0643\u0627\u0645",
    "Smiling face with halo": "\u0627\u0644\u0648\u062c\u0647 \u0627\u0644\u0645\u0628\u062a\u0633\u0645 \u0645\u0639 \u0647\u0627\u0644\u0629",
    "Smiling face with horns": "\u0627\u0644\u0648\u062c\u0647 \u0627\u0644\u0645\u0628\u062a\u0633\u0645 \u0628\u0642\u0631\u0648\u0646",
    "Winking face": "\u0627\u0644\u063a\u0645\u0632 \u0648\u062c\u0647",
    "Smiling face with smiling eyes": "\u064a\u0628\u062a\u0633\u0645 \u0648\u062c\u0647 \u0645\u0639 \u0639\u064a\u0648\u0646 \u062a\u0628\u062a\u0633\u0645",
    "Face savoring delicious food": "\u064a\u0648\u0627\u062c\u0647 \u0644\u0630\u064a\u0630 \u0627\u0644\u0645\u0630\u0627\u0642 \u0644\u0630\u064a\u0630 \u0627\u0644\u0637\u0639\u0627\u0645",
    "Relieved face": "\u0648\u062c\u0647 \u0628\u0627\u0644\u0627\u0631\u062a\u064a\u0627\u062d",
    "Smiling face with heart-shaped eyes": "\u0627\u0644\u0648\u062c\u0647 \u0627\u0644\u0645\u0628\u062a\u0633\u0645 \u0628\u0639\u064a\u0646\u064a\u0646 \u0639\u0644\u0649 \u0634\u0643\u0644 \u0642\u0644\u0628",
    "Smiling face with sunglasses": "\u0627\u0644\u0648\u062c\u0647 \u0627\u0644\u0645\u0628\u062a\u0633\u0645 \u0645\u0639 \u0627\u0644\u0646\u0638\u0627\u0631\u0627\u062a \u0627\u0644\u0634\u0645\u0633\u064a\u0629",
    "Smirking face": "\u0633\u0645\u064a\u0631\u0643\u064a\u0646\u062c \u0627\u0644\u0648\u062c\u0647",
    "Neutral face": "\u0645\u062d\u0627\u064a\u062f \u0627\u0644\u0648\u062c\u0647",
    "Expressionless face": "\u0648\u062c\u0647 \u0627\u0644\u062a\u0639\u0627\u0628\u064a\u0631",
    "Unamused face": "\u0644\u0627 \u0645\u0633\u0644\u064a\u0627 \u0627\u0644\u0648\u062c\u0647",
    "Face with cold sweat": "\u0648\u062c\u0647 \u0645\u0639 \u0639\u0631\u0642 \u0628\u0627\u0631\u062f",
    "Pensive face": "\u0648\u062c\u0647 \u0645\u062a\u0623\u0645\u0644",
    "Confused face": "\u0648\u062c\u0647 \u0627\u0644\u062e\u0644\u0637",
    "Confounded face": "\u0648\u062c\u0647 \u0645\u0631\u062a\u0628\u0643",
    "Kissing face": "\u062a\u0642\u0628\u064a\u0644 \u0627\u0644\u0648\u062c\u0647",
    "Face throwing a kiss": "\u0645\u0648\u0627\u062c\u0647\u0629 \u0631\u0645\u064a \u0642\u0628\u0644\u0629",
    "Kissing face with smiling eyes": "\u062a\u0642\u0628\u064a\u0644 \u0648\u062c\u0647 \u0645\u0639 \u0639\u064a\u0648\u0646 \u062a\u0628\u062a\u0633\u0645",
    "Kissing face with closed eyes": "\u062a\u0642\u0628\u064a\u0644 \u0648\u062c\u0647 \u0645\u0639 \u0639\u064a\u0648\u0646 \u0645\u063a\u0644\u0642\u0629",
    "Face with stuck out tongue": "\u0627\u0644\u0648\u062c\u0647 \u0645\u0639 \u062a\u0645\u0633\u0643 \u0628\u0647\u0627 \u0627\u0644\u0644\u0633\u0627\u0646",
    "Face with stuck out tongue and winking eye": "\u0627\u0644\u0648\u062c\u0647 \u0645\u0639 \u062a\u0645\u0633\u0643 \u0628\u0647\u0627 \u0627\u0644\u0644\u0633\u0627\u0646 \u0648\u0627\u0644\u0639\u064a\u0646 \u0627\u0644\u062a\u063a\u0627\u0636\u064a",
    "Face with stuck out tongue and tightly-closed eyes": "\u0627\u0644\u0648\u062c\u0647 \u0645\u0639 \u062a\u0645\u0633\u0643 \u0628\u0647\u0627 \u0627\u0644\u0644\u0633\u0627\u0646 \u0648\u0627\u0644\u0639\u064a\u0648\u0646 \u0645\u063a\u0644\u0642\u0629 \u0628\u0623\u062d\u0643\u0627\u0645\u002d",
    "Disappointed face": "\u0648\u062c\u0647\u0627 \u062e\u064a\u0628\u0629 \u0623\u0645\u0644",
    "Worried face": "\u0648\u062c\u0647\u0627 \u0627\u0644\u0642\u0644\u0642\u0648\u0646",
    "Angry face": "\u0648\u062c\u0647 \u063a\u0627\u0636\u0628",
    "Pouting face": "\u0627\u0644\u0639\u0628\u0648\u0633 \u0648\u062c\u0647",
    "Crying face": "\u0627\u0644\u0628\u0643\u0627\u0621 \u0627\u0644\u0648\u062c\u0647",
    "Persevering face": "\u0627\u0644\u0645\u062b\u0627\u0628\u0631\u0629 \u0648\u062c\u0647\u0647",
    "Face with look of triumph": "\u0648\u0627\u062c\u0647 \u0645\u0639 \u0646\u0638\u0631\u0629 \u0627\u0646\u062a\u0635\u0627\u0631",
    "Disappointed but relieved face": "\u0628\u062e\u064a\u0628\u0629 \u0623\u0645\u0644 \u0648\u0644\u0643\u0646 \u064a\u0639\u0641\u0649 \u0648\u062c\u0647",
    "Frowning face with open mouth": "\u0645\u0642\u0637\u0628 \u0627\u0644\u0648\u062c\u0647 \u0645\u0639 \u0641\u062a\u062d \u0627\u0644\u0641\u0645",
    "Anguished face": "\u0627\u0644\u0648\u062c\u0647 \u0627\u0644\u0645\u0624\u0644\u0645",
    "Fearful face": "\u0627\u0644\u0648\u062c\u0647 \u0627\u0644\u0645\u062e\u064a\u0641",
    "Weary face": "\u0648\u062c\u0647\u0627 \u0628\u0627\u0644\u0636\u062c\u0631",
    "Sleepy face": "\u0648\u062c\u0647 \u0646\u0639\u0633\u0627\u0646",
    "Tired face": "\u0648\u062c\u0647 \u0645\u062a\u0639\u0628",
    "Grimacing face": "\u0648\u062e\u0631\u062c \u0633\u064a\u0633 \u0627\u0644\u0648\u062c\u0647",
    "Loudly crying face": "\u0627\u0644\u0628\u0643\u0627\u0621 \u0628\u0635\u0648\u062a \u0639\u0627\u0644 \u0648\u062c\u0647\u0647",
    "Face with open mouth": "\u0648\u0627\u062c\u0647 \u0645\u0639 \u0641\u062a\u062d \u0627\u0644\u0641\u0645",
    "Hushed face": "\u0648\u062c\u0647\u0627 \u0627\u0644\u062a\u0643\u062a\u0645",
    "Face with open mouth and cold sweat": "\u0648\u0627\u062c\u0647 \u0645\u0639 \u0641\u062a\u062d \u0627\u0644\u0641\u0645 \u0648\u0627\u0644\u0639\u0631\u0642 \u0627\u0644\u0628\u0627\u0631\u062f",
    "Face screaming in fear": "\u0648\u0627\u062c\u0647 \u064a\u0635\u0631\u062e \u0641\u064a \u062e\u0648\u0641",
    "Astonished face": "\u0648\u062c\u0647\u0627 \u062f\u0647\u0634",
    "Flushed face": "\u0627\u062d\u0645\u0631\u0627\u0631 \u0627\u0644\u0648\u062c\u0647",
    "Sleeping face": "\u0627\u0644\u0646\u0648\u0645 \u0627\u0644\u0648\u062c\u0647",
    "Dizzy face": "\u0648\u062c\u0647\u0627 \u0628\u0627\u0644\u062f\u0648\u0627\u0631",
    "Face without mouth": "\u0648\u0627\u062c\u0647 \u062f\u0648\u0646 \u0627\u0644\u0641\u0645",
    "Face with medical mask": "\u0648\u0627\u062c\u0647 \u0645\u0639 \u0642\u0646\u0627\u0639 \u0627\u0644\u0637\u0628\u064a\u0629",

    // Line breaker
    "Break": "\u0627\u0644\u0627\u0646\u0642\u0633\u0627\u0645",

    // Math
    "Subscript": "\u0645\u0646\u062e\u0641\u0636",
    "Superscript": "\u062d\u0631\u0641 \u0641\u0648\u0642\u064a",

    // Full screen
    "Fullscreen": "\u0643\u0627\u0645\u0644 \u0627\u0644\u0634\u0627\u0634\u0629",

    // Horizontal line
    "Insert Horizontal Line": "\u0625\u062f\u0631\u0627\u062c \u062e\u0637 \u0623\u0641\u0642\u064a",

    // Clear formatting
    "Clear Formatting": "\u0625\u0632\u0627\u0644\u0629 \u0627\u0644\u062a\u0646\u0633\u064a\u0642",

    // Save
    "Save": "\u062d\u0641\u0638",

    // Undo, redo
    "Undo": "\u062a\u0631\u0627\u062c\u0639",
    "Redo": "\u0625\u0639\u0627\u062f\u0629",

    // Select all
    "Select All": "\u062a\u062d\u062f\u064a\u062f \u0627\u0644\u0643\u0644",

    // Code view
    "Code View": "\u0639\u0631\u0636 \u0627\u0644\u062a\u0639\u0644\u064a\u0645\u0627\u062a \u0627\u0644\u0628\u0631\u0645\u062c\u064a\u0629",

    // Quote
    "Quote": "\u0627\u0642\u062a\u0628\u0633",
    "Increase": "\u0632\u064a\u0627\u062f\u0629",
    "Decrease": "\u0627\u0646\u062e\u0641\u0627\u0636",

    // Quick Insert
    "Quick Insert": "\u0625\u062f\u0631\u0627\u062c \u0633\u0631\u064a\u0639",

    // Spcial Characters
    "Special Characters": "أحرف خاصة",
    "Latin": "لاتينية",
    "Greek": "الإغريقي",
    "Cyrillic": "السيريلية",
    "Punctuation": "علامات ترقيم",
    "Currency": "دقة",
    "Arrows": "السهام",
    "Math": "الرياضيات",
    "Misc": "متفرقات",

    // Print.
    "Print": "طباعة",

    // Spell Checker.
    "Spell Checker": "مدقق املائي",

    // Help
    "Help": "مساعدة",
    "Shortcuts": "اختصارات",
    "Inline Editor": "محرر مضمنة",
    "Show the editor": "عرض المحرر",
    "Common actions": "الإجراءات المشتركة",
    "Copy": "نسخ",
    "Cut": "يقطع",
    "Paste": "معجون",
    "Basic Formatting": "التنسيق الأساسي",
    "Increase quote level": "زيادة مستوى الاقتباس",
    "Decrease quote level": "انخفاض مستوى الاقتباس",
    "Image / Video": "صورة / فيديو",
    "Resize larger": "تغيير حجم أكبر",
    "Resize smaller": "تغيير حجم أصغر",
    "Table": "الطاولة",
    "Select table cell": "حدد خلية الجدول",
    "Extend selection one cell": "توسيع اختيار خلية واحدة",
    "Extend selection one row": "تمديد اختيار صف واحد",
    "Navigation": "التنقل",
    "Focus popup / toolbar": "التركيز المنبثقة / شريط الأدوات",
    "Return focus to previous position": "عودة التركيز إلى الموقف السابق",

    // Embed.ly
    "Embed URL": "تضمين عنوان ورل",
    "Paste in a URL to embed": "الصق في عنوان ورل لتضمينه",

    // Word Paste.
    "The pasted content is coming from a Microsoft Word document. Do you want to keep the format or clean it up?": "المحتوى الذي تم لصقه قادم من وثيقة كلمة ميكروسوفت. هل تريد الاحتفاظ بالتنسيق أو تنظيفه؟",
    "Keep": "احتفظ",
    "Clean": "نظيف",
    "Word Paste Detected": "تم اكتشاف معجون الكلمات"
  },
  direction: "rtl"
};

}));
