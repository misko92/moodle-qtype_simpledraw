Simple drawing question type
============================

`qtype_simpledraw` is a Moodle question type where the answer is a single freehand drawing,
optionally on top of a background image supplied by the teacher. It is built for students on
tablets (finger or stylus) and is graded manually.

It is a fork of the Freehand drawing question type (`qtype_drawing`) by ETH Zurich, maintained
by TU Wien (<https://github.com/tuwien-llt/moodle-qtype_drawing>), stripped down so students
get as few choices as possible.

What students get
-----------------

* A canvas (with the teacher's background image, if any) and a fullscreen button.
* Pen, eraser, undo and redo. Nothing else.
* One pen colour (black) and one pen size (4px). These are constants in `questiontype.php`
  (`STUDENT_COLOURS`, `PEN_SIZE`, `STUDENT_TOOLS`), not settings.

Removed compared with upstream: the menu bar, rulers, colour palette, pen-size presets, stroke
width/dash controls, the select/text/highlighter/line/rectangle/circle tools, and the matching
per-question and site settings.

What teachers get
-----------------

* The same question form as upstream minus the tool, colour and pen-size options: question
  text, background image, canvas size, and whether to show the question text inside the canvas.
* When reviewing an attempt, the full upstream editor for drawing feedback on top of the
  student's drawing (red pen by default).

Requirements
------------

Moodle 5.2.

Installation
------------

Copy the code into `question/type/simpledraw` and visit Site administration > Notifications.

Tests
-----

Behat: `vendor/bin/behat --config <behat.yml> --tags @qtype_simpledraw`.

License
-------

GNU GPL v3 or later, as upstream. Original copyright notices are kept in each file.
