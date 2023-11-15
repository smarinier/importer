# importer

Import from Text, HTML and EverNote export for NextCloud as MarkDown files

## Description

The Importer app is a NextCloud manual tool. It is meant to be used with the 'occ' command.

With this importer, you may import as MarkDown files:

- MarkDown files (.md)
- Text files (.txt)
- HTML files (.html)
- EverNote export result (.enex)

## Import from HTML

The conversion is base on <https://github.com/thephpleague/html-to-markdown>.

## Import from EverNote

In EverNote, you may select multiple notes, or directly export an entire notebook. It generates a .enex file, with all the text and the attachments embeded. (Take care of the maximum size).

### Notes

Importer will convert each note from an enex file as a markdown file in NextCloud. (EverNote notes are in HTML). 

### Attachments

The attachments from a note will be located in a .attachment folder, near the md file. Remember you may change NextCloud option (see Hidden Files) to see them, and keep in mind these attachments are removed if you remove the parent markdown file.

## Import command

```
Usage:escription:
Import files

Usage:
importer:import [options]

Options:
-N, --no                   don't import. Just show information about imported files.
-F, --file=FILE            use file
-U, --user=USER            User owner of the created files
-D, --directory=DIRECTORY  Target directory
-h, --help                 Display this help message
-q, --quiet                Do not output any message
-V, --version              Display this application version
--ansi                 Force ANSI output
--no-ansi              Disable ANSI output
-n, --no-interaction       Do not ask any interactive question
--no-warnings          Skip global warnings, show command output only
-v|vv|vvv, --verbose       Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

### Sample of EverNote import:

```
sudo -u www-data ./occ importer:import -F /tmp/MyExport.enex -U seb -D Notes/NoteBook1
```

### Notices:

- with -v option, you'll have a description of each created note and its attachments
- with -N option, same, but files are note created
- with -D option, folders are created if necessary
- when a note already exists, a "(XX)" is appended to make it unique
- the Importer requires the Text NextCloud application to be installed

