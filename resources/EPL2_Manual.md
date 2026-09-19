# EPL2_Manual.pdf
> Pagine: 332

> Convertito da `EPL2_Manual.pdf` il 2026-09-07 — testo estraibile + tabelle in markdown, una sezione per pagina.


---

## Pagina 1

EPL Programming
Guide

---

## Pagina 2

© 2007 ZIH Corp. The copyrights in this manual and the software and/or firmware in the label printer described
therein are owned by ZIH Corp. Unauthorized reproduction of this manual or the software and/or firmware in the
label printer may result in imprisonment of up to one year and fines of up to $10,000 (17U.S.C.506). Copyright
violators may be subject to civil liability.
This product may contain EPL™, EPL2™, ZPL®, ZPL II®, and ZebraLink™ programs; Element Energy Equalizer®
Circuit; E
3®;
and Monotype Imaging fonts. Software © ZIH Corp. All rights reserved worldwide.
ZebraLink and all product names and numbers are trademarks, and Zebra, the Zebra logo, ZPL, ZPL II, Element
3
Energy Equalizer Circuit, and E Circuit are registered trademarks of ZIH Corp. All rights reserved worldwide.
All other brand names, product names, or trademarks belong to their respective holders. For additional trademark
information, please see “Trademarks” on the product CD.
Proprietary Statement This manual contains proprietary information of Zebra Technologies Corporation and its
subsidiaries (“Zebra Technologies”). It is intended solely for the information and use of parties operating and
maintaining the equipment described herein. Such proprietary information may not be used, reproduced, or disclosed
to any other parties for any other purpose without the express, written permission of Zebra Technologies Corporation.
Product Improvements Continuous improvement of products is a policy of Zebra Technologies Corporation.
All specifications and designs are subject to change without notice.
Liability Disclaimer Zebra Technologies Corporation takes steps to ensure that its published Engineering
specifications and manuals are correct; however, errors do occur. Zebra Technologies Corporation reserves the right
to correct any such errors and disclaims liability resulting therefrom.
Limitation of Liability In no event shall Zebra Technologies Corporation or anyone else involved in the creation,
production, or delivery of the accompanying product (including hardware and software) be liable for any damages
whatsoever (including, without limitation, consequential damages including loss of business profits, business
interruption, or loss of business information) arising out of the use of, the results of use of, or inability to use such
product, even if Zebra Technologies Corporation has been advised of the possibility of such damages. Some
jurisdictions do not allow the exclusion or limitation of incidental or consequential damages, so the above limitation
or exclusion may not apply to you.
Part Number: 14245L-001 A

---

## Pagina 3

Contents
About This Document . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 15
Who Should Use This Document . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 16
How This Document Is Organized . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 16
Contacts . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 17
Web Site. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 17
The Americas . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 17
Europe, Africa, Middle East, and India. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 17
Asia Pacific. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 17
Document Conventions. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 18
1 • Introduction . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 19
What’s New in this Document. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 19
Command Conventions . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 20
Basic Command Syntax. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 20
Command Editor. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 21
Sending Commands Directly to the Printer. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 21
Sending Command Programming Files to the Printer . . . . . . . . . . . . . . . . . . . . . . . . 23
Transferring files with the Firmware Downloader . . . . . . . . . . . . . . . . . . . . . . . . . . . . 23
Downloading a File in the DOS Enviroment. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 24
Placing Elements in the Print Image. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 25
Text (Fonts). . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 26
Language Character Sets (Fonts 1-5) . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 29
Asian Character Sets (Fonts 8 & 9) . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 29
Chinese Character Set. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 29
Chinese Fonts 8 & 9 Double-Byte Ideographic Characters . . . . . . . . . . . . . . . . . . . . 30
Japanese Character Sets. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 30
Japanese Fonts 8 & 9 Double-Byte Ideographic Characters . . . . . . . . . . . . . . . . . . . 30
Korean Character Sets. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 31
Korean Font 8 Double-Byte Characters . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 31
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |
| --- |
|  |


---

## Pagina 4

4 Contents
Bar Codes. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 31
QR Code Bar Code. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 32
Programming Sequences Affect Graphic Results. . . . . . . . . . . . . . . . . . . . . . . . . . . . 32
2 • Printer Configuration . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 33
Explanation of the Status Printout. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 34
Determining Printer Firmware Version . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 35
Programming Mode Configuration . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 35
Manually Setting Line Mode. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 35
Media Detection . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 36
3 • EPL Commands . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 37
Memory and Command Usage. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 37
Command Description Memory Page. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 38
A ASCII Text . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 41
Variable Data and Counter Functions. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 44
Data with the RTC Time & Date Functions. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 45
A Simple Expressions in Data Fields . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 46
Asian Character Font Sets . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 47
AUTOFR Automatic Form Printing . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 48
B Bar Code . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 50
B RSS-14 Bar Code Specific Options . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 56
Data with the RTC Time & Date Functions. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 58
b 2D Bar Code – Aztec Specific Options . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 60
b 2D Bar Code – Aztec Mesa Specific Options . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 64
b 2D Bar Code – Data Matrix Specific Options . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 66
Automatic Data Matrix Bar Code Generation. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 68
b 2D Bar Code – MaxiCode Specific Options . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 70
Using AIM Specified MaxiCode Data Formatting . . . . . . . . . . . . . . . . . . . . . . . . . . . . 72
b 2D Bar Code – PDF417 Specific Options . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 74
PDF 417: General Information . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 78
PDF 417 Symbol Geometry . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 79
Automatic PDF 417 Bar Code Generation . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 79
b 2D Bar Code – QR Code Specific Options . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 81
C Counter . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 83
C Cut Immediate . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 85
D Density . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 86
dump Enable Dump Mode . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 87
EI Print Soft Font Information . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 88
EK Delete Soft Font . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 89
eR User Defined Error/Status Character . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 90
ES Store Soft Font . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 91
14245L-001 Rev. A EPL Programming Guide 5/30/07

---

## Pagina 5

Contents 5
f Cut Position . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 96
fB Adjust Backup Position . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 97
FE End Form Store . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 98
FI Print Form Information . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 99
FK Delete Form . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 100
FR Retrieve Form . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 101
FS Store Form . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 102
GG Print Graphics . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 103
GI Print Graphics Information . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 104
GK Delete Graphics . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 105
GM Store Graphics . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 106
GW Direct Graphic Write . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 108
i Asian Character Spacing . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 109
I Character Set Selection . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . .110
JB Disable Top Of Form Backup . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . .112
JC Disable Top Of Form Backup- All Cases . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . .113
JF Enable Top Of Form Backup . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . .114
LE Line Draw Exclusive OR . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . .115
LO Line Draw Black . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . .116
LS Line Draw Diagonal . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . .117
LW Line Draw White . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . .118
M Memory Allocation . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . .119
N Clear Image Buffer . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 120
o Cancel Software Options . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 121
oB Cancel Auto Bar Code Optimization . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 122
oE Line Mode Font Substitution . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 123
oH Macro PDF Offset . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 124
oM Disable Initial Esc Sequence Feed . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 126
oR Character Substitution (Euro) . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 127
oW Customize Bar Code Parameters . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 128
O Hardware Options . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 130
OEPL1 Set Line Mode . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 132
Returning to Page Mode. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 132
P Print . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 133
PA Print Automatic . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 134
q Set Label Width . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 135
Q Set Form Length . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 137
r Set Double Buffer Mode . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 140
R Set Reference Point . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 141
S Speed Select . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 142
5/30/07 EPL Programming Guide 14245L-001 Rev. A

---

## Pagina 6

6 Contents
TD Date Recall & Format Layout . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 143
TS Set Real Time Clock . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 144
TT Time Recall & Format Layout . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 145
U Print Configuration (General) . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 146
UA Enable Clear Label Counter Mode . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 147
UB Reset Label Counter Mode . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 148
UE External Font Information Inquiry . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 149
UF Form Information Inquiry . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 150
UG Graphics Information Inquiry . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 151
UI Host Prompts/Codepage Inquiry . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 152
UM Codepage & Memory Inquiry . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 153
UN Disable Error Reporting . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 154
UP Codepage & Memory Inquiry/Print . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 155
UQ Configuration Inquiry . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 156
US Enable Error Reporting . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 157
UT Enable Alternate Error Reporting . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 159
U% Host Prompts/Motor Temperature . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 160
U$ Host Prompts/Battery Status . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 161
V Define Variable . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 162
W Windows Mode . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 164
xa AutoSense . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 165
X Box Draw . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 166
Y Serial Port Setup . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 167
Z Print Direction . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 168
? Download Variables . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 170
^@ Reset Printer . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 171
^default Set Printer to Factory Defaults . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 172
^ee Error Report - Immediate . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 173
; Code Comment Line . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 174
4 • SGD Printer Setting Commands . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 175
Overview. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 176
setvar Command . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 176
getvar Command . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 176
do Command . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 177
Command Structure . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 178
How to Send Multiple SGD Commands . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 178
appl.bootblock. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 179
appl.name. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 180
device.languages . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 181
ip.active_network . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 182
14245L-001 Rev. A EPL Programming Guide 5/30/07

---

## Pagina 7

Contents 7
ip.ftp.enable . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 183
ip.http.enable . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 184
ip.lpd.enable . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 185
ip.pop3.enable . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 186
ip.pop3.password . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 187
ip.pop3.poll . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 188
ip.pop3.server_addr . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 189
ip.pop3.username. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 190
ip.primary_network . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 191
ip.smtp.domain . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 192
ip.smtp.enable. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 193
ip.smtp.server_addr . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 194
ip.snmp.get_community_name. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 195
ip.snmp.set_community_name. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 196
ip.telnet.enable . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 197
ip.snmp.enable . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 198
ip.tcp.enable . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 199
ip.udp.enable . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 200
media.printmode. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 201
odometer.headclean . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 203
odometer.headnew . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 204
odometer.label_dot_length . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 205
odometer.media_marker_count1 . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 206
odometer.media_marker_count2 . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 207
odometer.total_print_length . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 208
5 • SGD Wired Commands . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 209
Overview. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 210
setvar Command . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 210
getvar Command . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 210
do Command . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . .211
Command Structure . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 212
How to Send Multiple SGD Commands . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 212
external_wired.ip.addr. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 213
external_wired.ip.arp_interval. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 214
external_wired.ip.default_addr.enable . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 215
external_wired.ip.dhcp.cid_all. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 216
external_wired.ip.dhcp.cid_enable . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 217
external_wired.ip.dhcp.cid_prefix . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 218
external_wired.ip.dhcp.cid_suffix . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 219
external_wired.ip.dhcp.cid_type . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 220
external_wired.ip.gateway . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 221
external_wired.ip.netmask . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 222
external_wired.ip.port . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 223
5/30/07 EPL Programming Guide 14245L-001 Rev. A

---

## Pagina 8

8 Contents
external_wired.ip.protocol . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 224
external_wired.ip.timeout.enable . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 225
external_wired.ip.timeout.value . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 226
external_wired.mac_addr . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 227
internal_wired.ip.addr . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 228
internal_wired.ip.arp_interval . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 229
internal_wired.ip.default_addr.enable. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 230
internal_wired.ip.dhcp.cid_all . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 231
internal_wired.ip.dhcp.cid_enable. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 232
internal_wired.ip.dhcp.cid_prefix. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 233
internal_wired.ip.dhcp.cid_suffix. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 234
internal_wired.ip.dhcp.cid_type. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 235
internal_wired.ip.gateway. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 236
internal_wired.ip.netmask. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 237
internal_wired.ip.port. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 238
internal_wired.ip.protocol . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 239
internal_wired.ip.timeout.enable. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 240
internal_wired.ip.timeout.value. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 241
internal_wired.mac_addr. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 242
6 • SGD Wireless Commands . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 243
Overview. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 244
setvar Command . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 244
getvar Command . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 244
do Command . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 245
Command Structure . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 246
How to Send Multiple SGD Commands . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 246
card.inserted. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 247
card.mac_addr . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 248
ip.addr. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 249
ip.arp_interval. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 250
ip.bootp.enable. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 251
ip.dhcp.cid_all. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 252
ip.dhcp.cid_enable . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 253
ip.dhcp.cid_prefix . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 254
ip.dhcp.cid_suffix . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 255
ip.dhcp.cid_type . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 256
ip.dhcp.enable. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 257
ip.gateway. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 258
ip.netmask. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 259
ip.port . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 260
wlan.adhocautomode . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 261
wlan.adhocchannel. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 262
wlan.associated . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 263
14245L-001 Rev. A EPL Programming Guide 5/30/07

---

## Pagina 9

Contents 9
wlan.channel_mask . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 264
wlan.essid. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 265
wlan.firmware_version . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 266
wlan.ip.addr. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 267
wlan.ip.arp_interval. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 268
wlan.ip.default_addr.enable . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 269
wlan.ip.dhcp.cid_all. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 270
wlan.ip.dhcp.cid_enable . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 271
wlan.ip.dhcp.cid_prefix . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 272
wlan.ip.dhcp.cid_suffix . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 273
wlan.ip.dhcp.cid_type . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 274
wlan.ip.gateway . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 275
wlan.ip.netmask . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 276
wlan.ip.port . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 277
wlan.ip.protocol. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 278
wlan.ip.timeout.enable . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 279
wlan.ip.timeout.value . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 280
wlan.keep_alive.enable. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 281
wlan.keep_alive.timeout . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 282
wlan.kerberos.kdc. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 283
wlan.kerberos.password. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 284
wlan.kerberos.realm . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 285
wlan.kerberos.username. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 286
wlan.mac_addr . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 287
wlan.operating_mode . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 288
wlan.password . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 289
wlan.preamble. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 290
wlan.private_key_password . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 291
wlan.roam.interval. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 292
wlan.roam.signal. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 293
wlan.security. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 294
wlan.signal_noise . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 296
wlan.signal_quality . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 297
wlan.signal_strength. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 298
wlan.station_name . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 299
wlan.tx_power. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 300
wlan.tx_rate. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 301
wlan.username . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 302
wlan.wep.auth_type . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 303
wlan.wep.index. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 304
wlan.wep.key1 . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 305
wlan.wep.key2 . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 306
wlan.wep.key3 . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 307
wlan.wep.key4 . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 308
5/30/07 EPL Programming Guide 14245L-001 Rev. A

---

## Pagina 10

10 Contents
wlan.wep.key_format . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 309
wlan.wpa.psk . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 310
A • SGD Command Support . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 311
Printer and Firmware Compatibility. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 312
B • Character References . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 317
Default Character Map Code Page 437 . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 318
Euro Character. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 318
Dump Mode Character Map . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 319
C • 2746e Print Odometer . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 321
ELP2 Odometer Commands. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 322
oL Print Head Life Reminder Control . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 323
oLn Disable Print Head Life Reminder . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 324
oLy Enable Print Head Life Reminder . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 325
URH Print Head History Report . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 326
URL Read Print Odometers . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 327
URR Print Odometer Status Reporting . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 329
Index . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 331
14245L-001 Rev. A EPL Programming Guide 5/30/07

---

## Pagina 11

Functional Table of
Contents for EPL
Commands
2D Bar Code – Aztec Mesa Specific Options . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 64
2D Bar Code – Aztec Specific Options . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 60
2D Bar Code – Data Matrix Specific Options . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 66
2D Bar Code – MaxiCode Specific Options . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 70
2D Bar Code – PDF417 Specific Options . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 74
2D Bar Code – QR Code Specific Options . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 81
Adjust Backup Position . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 97
ASCII Text . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 41
Asian Character Spacing . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 109
Automatic Form Printing . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 48
AutoSense . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 165
Bar Code . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 50
Box Draw . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 166
Cancel Auto Bar Code Optimization . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 122
Cancel Software Options . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 121
Character Set Selection . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 110
Character Substitution (Euro) . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 127
Clear Image Buffer . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 120
Code Comment Line . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 174
Codepage & Memory Inquiry . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 153
Codepage & Memory Inquiry/Print . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 155
Configuration Inquiry . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 156
Counter . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 83
Customize Bar Code Parameters . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 128
Cut Immediate . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 85
Cut Position . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 96
Date Recall & Format Layout . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 143
Define Variable . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 162
Delete Form . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 100
5/30/07 EPL Programming Guide 13979L-002 Rev. A

|  |
| --- |
|  |


---

## Pagina 12

12 Functional Table of Contents for EPL Commands
Delete Graphics . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 105
Delete Soft Font . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 89
Density . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 86
Direct Graphic Write . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 108
Disable Error Reporting . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 154
Disable Initial Esc Sequence Feed . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 126
Disable Print Head Life Reminder . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 324
Disable Top Of Form Backup . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 112
Disable Top Of Form Backup- All Cases . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 113
Download Variables . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 170
Enable Alternate Error Reporting . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 159
Enable Clear Label Counter Mode . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 147
Enable Dump Mode . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 87
Enable Error Reporting . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 157
Enable Print Head Life Reminder . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 325
Enable Top Of Form Backup . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 114
End Form Store . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 98
Error Report - Immediate . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 173
External Font Information Inquiry . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 149
Form Information Inquiry . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 150
Graphics Information Inquiry . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 151
Hardware Options . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 130
Host Prompts/Battery Status . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 161
Host Prompts/Codepage Inquiry . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 152
Host Prompts/Motor Temperature . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 160
Line Draw Black . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 116
Line Draw Diagonal . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 117
Line Draw Exclusive OR . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 115
Line Draw White . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 118
Line Mode Font Substitution . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 123
Macro PDF Offset . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 124
Memory Allocation . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 119
Print . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 133
Print Automatic . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 134
Print Configuration (General) . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 146
Print Direction . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 168
Print Form Information . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 99
Print Graphics . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 103
Print Graphics Information . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 104
Print Head History Report . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 326
Print Head Life Reminder Control . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 323
Print Odometer Status Reporting . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 329
Print Soft Font Information . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 88
Read Print Odometers . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 327
13979L-002 Rev. A EPL Programming Guide 5/30/07

---

## Pagina 13

Functional Table of Contents for EPL Commands 13
Reset Label Counter Mode . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 148
Reset Printer . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 171
Retrieve Form . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 101
RSS-14 Bar Code Specific Options . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 56
Serial Port Setup . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 167
Set Double Buffer Mode . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 140
Set Form Length . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 137
Set Label Width . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 135
Set Line Mode . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 132
Set Printer to Factory Defaults . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 172
Set Real Time Clock . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 144
Set Reference Point . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 141
Simple Expressions in Data Fields . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 46
Speed Select . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 142
Store Form . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 102
Store Graphics . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 106
Store Soft Font . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 91
Time Recall & Format Layout . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 145
User Defined Error/Status Character . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 90
Windows Mode . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 164
5/30/07 EPL Programming Guide 13979L-002 Rev. A

---

## Pagina 14

14 Functional Table of Contents for EPL Commands
Notes • ___________________________________________________________________
__________________________________________________________________________
__________________________________________________________________________
__________________________________________________________________________
__________________________________________________________________________
__________________________________________________________________________
__________________________________________________________________________
__________________________________________________________________________
__________________________________________________________________________
__________________________________________________________________________
13979L-002 Rev. A EPL Programming Guide 5/30/07

---

## Pagina 15

About This Document
This section provides you with contact information, document structure and organization, and
additional reference documents.
Contents
Who Should Use This Document. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 16
How This Document Is Organized. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 16
Contacts. . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 17
Document Conventions . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . 18
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |
| --- |
|  |


---

## Pagina 16

16 About This Document
Who Should Use This Document
Who Should Use This Document
This Guide is for programmers who are familiar working with programming languages.
How This Document Is Organized
The Guide is set up as follows:
Section Description
Introduction Provides a high-level overview about this guide
and EPL.
Printer Configuration Covers interpreting your printer’s configuration
setup and setting basic modes.
EPL Commands Contains the complete alphabetical listing of EPL
commands.
SGD Printer Setting Commands Provides a high-level overview of printer setting
Set/Get/Do (SGD) commands.
SGD Wired Commands Provides a high-level overview of the wired
Set/Get/Do (SGD) commands.
SGD Wireless Commands Provides a high-level overview of the wireless
Set/Get/Do (SGD) commands.
Appendixes The appendixes include:
Character References
2746e Print Odometer
SGD Command Support
14245L-001 Rev. A EPL Programming Guide 5/30/07

---

## Pagina 17

About This Document 17
Contacts
Contacts
You can contact Zebra Technologies at the following:
Web Site
http://www.zebra.com
Technical Support via the Internet is available 24 hours per day, 365 days per year. Go to
http://www.zebra.com/support.
The Americas
Regional Headquarters Technical Support Customer Service Dept.
Zebra Technologies International, T: +1 847 913 2259 For printers, parts, media, and ribbon,
LLC F: +1 847 913 2578 please call your distributor, or
333 Corporate Woods Parkway Hardware: ts1@zebra.com contactus.
Vernon Hills, Illinois 60061.3109 Software: ts3@zebra.com T: +1 877 ASK ZEBRA (275 9327)
U.S.A E: clientcare@zebra.com
T: +1 847 793 2600
Toll-free +1 800 423 0422
F: +1 847 913 8766
Europe, Africa, Middle East, and India
Regional Headquarters Technical Support Internal Sales Dept.
Zebra Technologies Europe Limited T: +44 (0) 1494 768298 For printers, parts, media, and ribbon,
Zebra House F: +44 (0) 1494 768210 please call your distributor, or
The Valley Centre, Gordon Road Germany: Tsgermany@zebra.com contactus.
High Wycombe, HP13 6EQ, UK France: Tsfrance@zebra.com T: +44 (0) 1494 768316
Buckinghamshire Spain/Portugal: Tsspain@zebra.com F: +44 (0) 1494 768244
T: +44 (0)1494 472872 All other areas: Tseurope@zebra.com E: cseurope@zebra.com
F: +44 (0) 1494 450103
Asia Pacific
Regional Headquarters Technical Support Customer Service
Zebra Technologies Asia Pacific, LLC T: +65 6858 0722 For printers, parts, media, and ribbon,
120 Robinson Road F: +65 6885 0838 please call your distributor, or
#06-01 Parakou Building E: China: tschina@zebra.com contactus.
Singapore 068913 All other areas: T: +65 6858 0722
T: +65 6858 0722 tsasiapacific@zebra.com F: +65 6858 0836
F: +65 6885 0838
5/30/07 EPL Programming Guide 14245L-001 Rev. A

| Technical Support |
| --- |
| T: +1 847 913 2259<br>F: +1 847 913 2578<br>Hardware: ts1@zebra.com<br>Software: ts3@zebra.com |


| Technical Support |
| --- |
| T: +44 (0) 1494 768298<br>F: +44 (0) 1494 768210<br>Germany: Tsgermany@zebra.com<br>France: Tsfrance@zebra.com<br>Spain/Portugal: Tsspain@zebra.com<br>All other areas: Tseurope@zebra.com |


| Technical Support |
| --- |
| T: +65 6858 0722<br>F: +65 6885 0838<br>E: China: tschina@zebra.com<br>All other areas:<br>tsasiapacific@zebra.com |


---

## Pagina 18

18 About This Document
Document Conventions
Document Conventions
The following conventions are used throughout this document to convey certain information.
Alternate Color (online only) Cross-references contain hot links to other sections in this
guide. If you are viewing this guide online in .pdf format, you can click the cross-reference
(blue text) to jump directly to its location.
Command Line Examples Command line examples appear in Courier New font. For
example, type ZTools to get to the Post-Install scripts in the bin directory.
Files and Directories File names and directories appear in Courier New font. For
example, the Zebra<version number>.tar file and the /root directory.
Icons Used
Important • Advises you of information that is essential to complete a task.
Note • Indicates neutral or positive information that emphasizes or supplements important
points of the main text.
Example • Provides an example, often a scenario, to better clarify a section of text.
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 19

1
Introduction
This section contains information about the basic features, command syntax, and terminology
of the EPL2 programming language for Zebra's desktop printers with flash memory
architecture. These printers incorporate common programming code sets and architectural
features.
The primary operating mode for the printer is EPL2, a page description language. EPL2 is an
ideal language for your labeling and bar code requirements. To speed printing, it is designed to
assemble all of the elements of the label prior to printing. EPL2 is versatile and capable of
printing graphics, a wide range media, and bar codes.
Some direct thermal printer models also include a legacy printer compatibility mode, Line
Mode. Line Mode supports our early model EPL programming language - ELP1. A separate
manual is provided for Line Mode printing. See the printer’s Software and Documentation CD
for the programmer’s manual that applies to your printer, or visit our web site at:
www.zebra.com
What’s New in this Document
Newly added to this programming guide are Set/Get/Do (SGD) commands. SGD commands
are commands that allow you to configure printers and have the printer perform the specified
function immediately after receiving the command.
See SGD Command Support onpage311 to determine if these commands are compatible with
your printer/firmware combination.
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |
| --- |
|  |


---

## Pagina 20

20 Introduction
Command Conventions
The manual uses the following typographic conventions to describe commands.
Example Description
A Commands (case sensitive)
p1,p2,p3 Required parameters
[p,p,p] Optional parameters
{Choice 1|Choice2} Indicates a mandatory choice between two or more items. You
must include one of the items unless all of the items are also
enclosed in square brackets.
“NAME” The name of a form or graphic in double quote marks.
“DATA” The text or bar code data in double quote marks. The
backslash (\) character designates the following character is a
literal and will encode into the data field.
To Print Enter into data field
" \"
“Company” \”Company\”
\ \\
\code\ \\code\\
“PROMPT” An ASCII text field that will be transmitted to the host (via the
serial interface) each time this command is executed.
Important • All commands and alpha character command parameters are case sensitive!
Basic Command Syntax
Each command consists of one or two ASCII (case sensitive) alpha characters to identify the
specific command desired. Some commands require one or more additional parameters to
supply the printer with sufficient information to complete the command. Refer to Figure 1- for
the basic command syntax.
Figure 1 • Basic Command Syntax
CommandName
WRITE TEXT
Ap,p,p,p,p,p,p,"DATA"
1 2 3 4 5 6 7
Command Parameters Command
Specific
Data
14245L-001 Rev. A EPL Programming Guide 5/30/07

---

## Pagina 21

Introduction 21
Each command line must be terminated with a Line Feed (LF) character (Dec. 10). Most PC
based systems send CR/LF when the Enter key is pressed. The Carriage Return (CR) character
is ignored by the printer and cannot be used in place of LF.
Command Editor
One method to create command files is through an ASCII-based text editor. In the DOS
environment, MS-DOS EDIT or BRIEF are good choices. In the Windows environment,
TextPad® for Windows is a good choice and is available for download of a free evaluation
copy at: www.textpad.com
Sending Commands Directly to the Printer
The easiest method available in the Microsoft Windows environment is to use the Zebra
Universal Printer Driver (ZUD) for sending single EPL Page Mode commands to the printer.
This method supports all of the printer interfaces. If you have successfully installed the printer
and its printer driver, you can use the following procedure:
1. Find and open your printer in the Windows Start > Settings > Printers and Faxes selection.
2. In the newly opened Printers and Faxes window, left click a single time to select your
printer and then right click to open a menu window.
3. Click on the Properties menu selection.
5/30/07 EPL Programming Guide 14245L-001 Rev. A

---

## Pagina 22

22 Introduction
4. Click on the Printer Tab.
Printer Tab
Click to expand
(Advanced)
5. Click on the plus sign in front of Advanced to expand the selections available.
6. Click on the Tools button.
7. Click in Direct Command for Printer and begin typing EPL Page Mode printer commands.
Press the Send button to send a single command. Do not use the Enter key on the key
board; it is the same thing as pressing the OK button (to exit the Tools window).
14245L-001 Rev. A EPL Programming Guide 5/30/07

---

## Pagina 23

Introduction 23
Sending Command Programming Files to the Printer
Page Mode programming files can be sent to the printer from Windows operating systems by
using the Zebra Firmware Downloader (found on the user’s CD or at www.zebra.com. In the
DOS enviroment, use the Copy command at the command prompt. The easiest method is to
use the Zebra Firmware Downloader to transfer files to the printer.
Transferring files with the Firmware Downloader
1. Install the printer. Verify communication is working properly by printing a Printer
Configuration or Test using the printer's driver properties window. See Sending
Commands Directly to the Printer onpage21 for more details.
2. Install the Zebra Firmware Downloader on your system by selecting Start > Programs >
Zebra > Firmware Downloader.
3. From the Printer menu, select Auto-Detect.
5/30/07 EPL Programming Guide 14245L-001 Rev. A

---

## Pagina 24

24 Introduction
4. Click on your Zebra printer to select the printer for download.
5. From the File menu, choose Select Firmware File.
Use the Browser window to select your command file for download to your printer.
Downloading a File in the DOS Enviroment
To execute the file, use the editor’s print command or from the DOS prompt, use the COPY
command to send the file directly to the printer. This method does not support USB or Ethernet
printer interfaces.
An example of the use of the COPY command is:
COPY “FILENAME.EXT” LPT1
or
COPY FILENAME.EXT” COM1
For more information on the use of the COPY command, refer to your DOS software manual.
Configure the COM port to match the printer’s serial port setting (typically set to defaults). See
the Y command in section 2 for details.
14245L-001 Rev. A EPL Programming Guide 5/30/07

---

## Pagina 25

Introduction 25
Placing Elements in the Print Image
Image elements are located in the image print buffer on an X-Y grid expressed in dots. The X
value represents the width and the Y value represents the height of the grid.
The point of origin (the starting point) for a non-rotated object is the upper left corner. As an
object rotates, the point of origin rotates with the object.
These image buffer properties are depicted graphically in the following illustration.
Figure 2 • Sample Format
(cid:89)(cid:1)(cid:66)(cid:89)(cid:74)(cid:84)
(cid:90)(cid:1)(cid:66)(cid:89)(cid:74)(cid:84) (cid:9)(cid:17)(cid:13)(cid:17)(cid:10)
(cid:55)(cid:72)(cid:91)(cid:87)
(cid:3)(cid:50)(cid:3)
(cid:69)(cid:80)(cid:85)(cid:84)
(cid:37)(cid:74)(cid:83)(cid:70)(cid:68)(cid:85)(cid:74)(cid:80)(cid:79)(cid:1)(cid:48)(cid:71)(cid:1)(cid:39)(cid:70)(cid:70)(cid:69)
The minimum non-printing margin on all edges of the label is 1 mm. Printing closer than 1
mm to the top or bottom edge of the label may cause the printer to advance unwanted labels or
cause the printer to go into error condition.
5/30/07 EPL Programming Guide 14245L-001 Rev. A
(cid:55)(cid:72)(cid:91)(cid:87)
(cid:87)(cid:91)(cid:72)(cid:55)
(cid:3)(cid:82)(cid:3)(cid:1)(cid:69)(cid:80)(cid:85)(cid:84)
(cid:49)(cid:80)(cid:74)(cid:79)(cid:85)(cid:1)(cid:80)(cid:71) (cid:49)(cid:80)(cid:74)(cid:79)(cid:85)(cid:1)(cid:80)(cid:71)
(cid:48)(cid:83)(cid:74)(cid:72)(cid:74)(cid:79)(cid:1)(cid:71)(cid:80)(cid:83) (cid:48)(cid:83)(cid:74)(cid:72)(cid:74)(cid:79)(cid:1)(cid:71)(cid:80)(cid:83)
(cid:53)(cid:70)(cid:89)(cid:85)(cid:1)(cid:48)(cid:67)(cid:75)(cid:70)(cid:68)(cid:85) (cid:53)(cid:70)(cid:89)(cid:85)(cid:1)(cid:48)(cid:67)(cid:75)(cid:70)(cid:68)(cid:85)
(cid:51)(cid:80)(cid:85)(cid:66)(cid:85)(cid:70)(cid:69)(cid:1)(cid:26)(cid:17)(cid:161)
(cid:49)(cid:80)(cid:74)(cid:79)(cid:85)(cid:1)(cid:80)(cid:71)
(cid:48)(cid:83)(cid:74)(cid:72)(cid:74)(cid:79)(cid:1)(cid:71)(cid:80)(cid:83)
(cid:35)(cid:66)(cid:83)(cid:36)(cid:80)(cid:69)(cid:70)(cid:1)(cid:48)(cid:67)(cid:75)(cid:70)(cid:68)(cid:85)
(cid:49)(cid:80)(cid:74)(cid:79)(cid:85)(cid:1)(cid:80)(cid:71)
(cid:48)(cid:83)(cid:74)(cid:72)(cid:74)(cid:79)(cid:1)(cid:71)(cid:80)(cid:83)
(cid:53)(cid:70)(cid:89)(cid:85)(cid:1)(cid:48)(cid:67)(cid:75)(cid:70)(cid:68)(cid:85)
(cid:51)(cid:80)(cid:85)(cid:66)(cid:85)(cid:70)(cid:69)(cid:1)(cid:19)(cid:24)(cid:17)(cid:161)

| (cid:9)(cid:17)(cid:13)(cid:17)(cid:10) |  |  |  |  |  |  |  |  |  |  |  |  |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
|  |  |  | (cid:55) | (cid:72)(cid:91)(cid:87) |  |  |  |  |  | (cid:55) |  |  |
|  |  |  |  |  |  |  |  |  |  | (cid:72) |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  | (cid:91)(cid:87) |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  | (cid:87)(cid:91) |  |  |
|  |  |  |  |  |  |  |  |  |  | (cid:72) |  |  |
|  |  |  |  |  |  |  |  |  |  | (cid:55) |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  | (cid:37)(cid:74)(cid:83) | (cid:70)(cid:68)(cid:85)(cid:74)(cid:80)(cid:79)(cid:1)(cid:48)(cid:71)(cid:1) | (cid:39)(cid:70)(cid:70)(cid:69) |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |


---

## Pagina 26

26 Introduction
Text (Fonts)
The standard EPL2 printer has five (1-5) resident mono-spaced dot fonts. Fonts A-Z and a-z
(upper and lower case alpha characters) are reserved for downloading soft fonts.
Figure 3 • Character spacing
First Character of Text String Reference Point
12
D
o
t
s
8 Dots
Character Width
Inter-character Space
(actually white dots)
Control text height (in horizontal dots) and width (in vertical dots) with the horizontal and
vertical multipliers. The text is oriented first and then the A command’s font multipliers and
font rotation are applied.
Note • The reference point of the first character in a text string is not affected by the font size
multiplier values.
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |  |  |  |  |  |  |  |  |  |  |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
|  |  |  |  |  |  |  |  |  |  |  | 1 |
|  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |


---

## Pagina 27

Introduction 27
The standard Latin font (1-5) and soft font characters are dot mapped differently than the
Asian font (8 & 9) characters. The Asian character does not have a built-in inter-character gap.
The Latin characters include a single dot border around each character.
TotalCharacterWidth Note:203dpicharactersshownasexample
8Dots
CharacterWidth TotalCharacterWidth
24Dots
12
D
o
t
s
24
D
o
t
s
Inter-characterSpace
(actuallywhitedots)
Fonts 1 (8 x 12 dots) Font 8 (24 x 24 dots)
With the (A) command’s horizontal multiplier (p5) set to 2, the inter-character spacing will
look like the following example.
2 times 2 times
2 times 2 times
5/30/07 EPL Programming Guide 14245L-001 Rev. A

| TotalCharacterWidth<br>8Dots<br>CharacterWidth |  |  |  |  |  |  |
| --- | --- | --- | --- | --- | --- | --- |
|  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |


|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |


|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |


|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |


---

## Pagina 28

28 Introduction
Text is placed into the image buffer. See the following example.
g
HorizontalDots
xaxis
(0,0)
yaxis
Text-NoSizeMultipliers
Pointof Pointof
Originfor Originfor
TextObject 2xHorizontalMultiplier V TextObject
e Rotated90°
r
t
Pointof i
c
Originfor a
TextObject l
2xVerticalMultiplier D
o
t
s
Pointof
Originfor
TextObject
DirectionOfFeed
PointofOrigin
forTextObject
Rotated270°
The Asian fonts (8-9) can print character strings oriented from top to bottom (p values 4–7),
3
as well as the standard Latin word orientation from left to right (p values 0–3). The characters
3
will print in the sequence that they are entered into the (A) command’s data field.
HorizontalDots
xaxis
(0,0)
yaxis
Text-NoSizeMultipliers
Pointof Pointof
Originfor Originfor
TextObject V TextObject
(p=5) 2xHorizontalMultiplier e Rotated90°
r
t (p=6)
Pointof i
c
Originfor a
TextObject l
(p=5) D
o
2xVerticalMultiplier t
s
Pointof
Originfor
TextObject
(p=5) DirectionOfFeed
PointofOrigin
forTextObject
Rotated270°
(p=4)
14245L-001 Rev. A EPL Programming Guide 5/30/07

| (0,0) |  |  |  |  |  |  |  |  |  |  |  |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
|  |  |  |  |  |  | Te | xt- | NoSi | zeMul | tipliers |  |
|  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  | 2xHo | rizo |  | nta | lM | ultipl | ier |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  | 2x | Ver |  | tic | alM | ultip | lier |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  | Directi | onO |  | fFe | ed |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |


| (0,0) |  |  |  |  |  |  |  |  |  |  |  |  |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
|  |  |  | Tex |  | t-N | o | Size | Mul | tipliers |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  | 2x | Hor | izon | tal | Multipli | er |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  | 2x |  | Ver | tic | alM | ultip | lier |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  | Directio |  | nO | fFe | ed |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |


---

## Pagina 29

Introduction 29
Language Character Sets (Fonts 1-5)
The Latin-based language and Greek character support is controlled and enabled with the I
command onpage110. The default language set is the English DOS codepage 437.
Characters, such as the Euro symbol, can be substituted using the oR command onpage127.
Asian Character Sets (Fonts 8 & 9)
The Page Mode EPL2 programming language supports up to two (2) font sets of a single Asian
language as well as the standard EPL2 Latin (Multilingual) fonts 1-5 and downloadable soft
fonts (A-Z and a-z).
Asian language support is an optional feature and requires a special version of the printer
(PCBA) to support the large Asian character sets. The flash-based printers support up to five
different Asian language character (ideogram) sets. See the A command onpage41 for the
detailed list of of fonts.
For Asian language firmware updates, publications, and support options, see www.zebra.com.
All fonts can be expanded both horizontally and vertically. The Asian fonts can also be printed
from top to bottom or in the Latin character orientation from left to right. The Asian printers
support the standard Latin fonts with the single character map code page 437 for all five (5)
fonts.
The Asian characters are 16 bit (or double-byte) mapped characters. The printed Asian
character is dependent on the double-byte ASCII values. The Latin (English, etc.) font sets are
8 bits per (or single-byte) ASCII character maps.
Note • Only one (1) Asian language is supported by a printer. Each Asian language
(character set) is a separate printer firmware version.
Asian printers with flash firmware can be reprogrammed for a different Asian language, but
we do not recommend this for normal use.
Chinese Character Set
The printer automatically recognizes single-byte characters and double-byte characters.
The single-byte font characters are mapped to hexadecimal address range 00 to 7F hex (0-127
decimal) for 203 dpi printers and 00 to FF hex (0-255 decimal) for 300 dpi printers.
The double-byte font characters are mapped to hexadecimal address range A1A0 to F7FF hex.
First byte, 161 (A1h) and second byte 160 (A0h) to first byte, 247 (F7h) and second byte, 255
(FFh) is the decimal, grouped byte range.
5/30/07 EPL Programming Guide 14245L-001 Rev. A

---

## Pagina 30

30 Introduction
Chinese Fonts 8 & 9 Double-Byte Ideographic Characters
The Chinese ideographic font characters are mapped to double-byte hexadecimal address
range A1A0 to F7FFhex.
See the following pages for the codepage character mappings.
• Simplified — 203 dpi printers
• Simplified — 300 dpi printers
• Traditional — 300 dpi printers
Japanese Character Sets
The printer automatically recognizes single-byte characters and double-byte characters in the
Shift-JIS codepage. The single-byte font characters are mapped to hexadecimal address range
00 to 7F hex (0-127 decimal) for 203 dpi printers and 00 to FF hex (0-255 decimal) for 300 dpi
printers.
The double-byte font characters are mapped to the hexadecimal address ranges in the
following table.
From To
Codepage
Range
Reference
1st byte 2nd byte 1st byte 2nd byte
JIS 2120-7424 33 (21h) 32 (20h) 116 (74h) 36 (24h)
Shift-JIS 8140-9FFC 129(81h) 64 (40h) 159 (9Fh) 252 (FCh)
E040-EAA4 224 (E0h) 64 (40h) 234 (EAh) 164 (A4h)
Japanese Fonts 8 & 9 Double-Byte Ideographic Characters
The Japanese ideographic font characters are mapped to double-byte hexadecimal addresses.
See the following electronic document (Acrobat format) pages for codepage character maps.
• 203 dpi printers — JIS
• 203 dpi printers — Shift JIS
• 300 dpi printers — JIS
• 300 dpi printers — Shift JIS
14245L-001 Rev. A EPL Programming Guide 5/30/07

| Range | From<br>1st byte 2nd byte |  |  |
| --- | --- | --- | --- |
| 2120-7424 | 33 (21h) | 32 (20h) | 116 (74h) |
| 8140-9FFC | 129(81h) | 64 (40h) | 159 (9Fh) |
| E040-EAA4 | 224 (E0h) | 64 (40h) | 234 (EAh) |


---

## Pagina 31

Introduction 31
Korean Character Sets
The printer automatically recognizes single-byte characters and double-byte characters.
The single-byte font characters are mapped to hexadecimal address range 00 to 7F hex (0-127
decimal).
The double-byte font characters are mapped to hexadecimal address range A1A0 to F0FF hex.
First byte, 161 (A1h) and second byte 160 (A0h) to first byte, 253 (FDh) and second byte, 255
(FFh) is the decimal, grouped byte range.
Korean Font 8 Double-Byte Characters
The Korean font characters are mapped to double-byte hexadecimal address range A1A0 to
FDFF hex. See the following pages for the character maps.
• 203 dpi printers
• 300 dpi printers
Bar Codes
All bar codes supported by the EPL2 language have associated industry specifications that the
programmer should be aware of and adhere to. The programmer needs to consider bar code
features and requirements when choosing and using a bar code for different applications. Some
of the features and requirements that need consideration are listed below:
• Data used by the application are per the bar code specification (numbers only,
alphanumeric, alphanumeric and special characters, etc.).
• Minimum and maximum number of characters allowed or required per bar code.
• Density or magnification of a given bar code type.
• White area required around bar codes (the “Quiet Zone”).
• The bar code must print within the image buffer (printable area of the label).
5/30/07 EPL Programming Guide 14245L-001 Rev. A

---

## Pagina 32

32 Introduction
Note • Bar Code Orientation Tip: To help ensure that generated bar codes are readable by the
widest variety of bar code readers, print bar codes in the “Picket Fence” orientation versus
the “Ladder” orientation.
(cid:41)(cid:80)(cid:83)(cid:74)(cid:91)(cid:80)(cid:79)(cid:85)(cid:66)(cid:77)(cid:1)(cid:37)(cid:80)(cid:85)(cid:84)
(cid:89)(cid:1)(cid:66)(cid:89)(cid:74)(cid:84)
(cid:9)(cid:17)(cid:13)(cid:17)(cid:10)
(cid:90)(cid:1)(cid:66)(cid:89)(cid:74)(cid:84)
(cid:41)(cid:70)(cid:74)(cid:72)(cid:73)(cid:85)(cid:1)(cid:80)(cid:71)
(cid:35)(cid:66)(cid:83)(cid:1)(cid:36)(cid:80)(cid:69)(cid:70)
(cid:41)(cid:70)(cid:74)(cid:72)(cid:73)(cid:85)(cid:1)(cid:80)(cid:71)
(cid:35)(cid:66)(cid:83)(cid:1)(cid:36)(cid:80)(cid:69)(cid:70)
(cid:55)
(cid:70)
(cid:83)
(cid:85)
(cid:74)
(cid:68)
(cid:66)
(cid:49)(cid:74)(cid:68)(cid:76)(cid:70)(cid:85)(cid:1)(cid:39)(cid:70)(cid:79)(cid:68)(cid:70) (cid:77)
(cid:48)(cid:83)(cid:74)(cid:70)(cid:79)(cid:85)(cid:66)(cid:85)(cid:74)(cid:80)(cid:79) (cid:37)
(cid:80)
(cid:85)
(cid:84)
(cid:45)(cid:66)(cid:69)(cid:69)(cid:70)(cid:83)
(cid:48)(cid:83)(cid:74)(cid:70)(cid:79)(cid:85)(cid:66)(cid:85)(cid:74)(cid:80)(cid:79)
(cid:37)(cid:74)(cid:83)(cid:70)(cid:68)(cid:85)(cid:74)(cid:80)(cid:79)(cid:1)(cid:48)(cid:71)(cid:1)(cid:39)(cid:70)(cid:70)(cid:69)
QR Code Bar Code
The QR Code bar code is only offered with Japanese Character bar code printer
configurations.
Programming Sequences Affect Graphic Results
Graphic elements can interact and the resultant image can be affected by other commands.
Structure command sequences to reduce the chances of unexpected print results. The printer
will process lines, text, boxes, and most bar codes in command sequence. The printer then
processes the printer control processes, counters, variable data, Postnet, and then graphics last.
14245L-001 Rev. A EPL Programming Guide 5/30/07

| (cid:9)(cid:17)(cid:13)(cid:17)(cid:10) |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
|  |  |  |  |  |  |  |  |  |  |  |  |  | (cid:41)(cid:70)(cid:74)(cid:72) | (cid:73)(cid:85)(cid:1)(cid:80)(cid:71) |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  | (cid:35)(cid:66)(cid:83)(cid:1)(cid:36) | (cid:80)(cid:69)(cid:70) |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  | (cid:49)(cid:74)(cid:68) |  | (cid:76)(cid:70)(cid:85)(cid:1)(cid:39) | (cid:70)(cid:79)(cid:68) | (cid:70) |  |  |  |  |  |  |  |  |
|  |  |  | (cid:48)(cid:83)(cid:74) |  | (cid:70)(cid:79)(cid:85) | (cid:66)(cid:85)(cid:74)(cid:80) | (cid:79) |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  | (cid:45) | (cid:66)(cid:69)(cid:69)(cid:70) | (cid:83) |  |  |  |  |
|  |  |  |  |  |  |  |  |  | (cid:48)(cid:83)(cid:74) | (cid:70)(cid:79)(cid:85)(cid:66)(cid:85) | (cid:74)(cid:80)(cid:79) |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  | (cid:37)(cid:74)(cid:83) | (cid:70)(cid:68)(cid:85)(cid:74) | (cid:80)(cid:79)(cid:1)(cid:48) | (cid:71)(cid:1)(cid:39)(cid:70)(cid:70) | (cid:69) |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |


---

## Pagina 33

2
Printer Configuration
This section covers interpreting your printer’s configuration setup and setting basic modes. To
determine your printer’s configuration and operating mode, do one of the following:
• Print a configuration label using the control panel.
• Print a configuration label by sending the U command to the printer.
• Use the AutoSense feature.
The primary functions that AutoSense provides are:
• adjustment of the Media Sensor in the printer to the media in use
• the programming mode — page (EPL2) or line (EPL1 emulation) mode.
• the printer’s serial interface settings
• the printer’s configuration status including printer options
Note • The AutoSense feature and Line Mode are not available for all printer models.
Refer to your printer’s user guide for specific feature information.
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |
| --- |
|  |


---

## Pagina 34

34 Printer Configuration
Explanation of the Status Printout
Depending on your printer model, your dump mode label will look similar to one of those
shown below. See Figure9 onpage319 for additional information.
Figure 4 • Dump Mode Print Samples
A. PrinterI.D.codenumberandfirmwareversion.
UKQ1935 V4.59
B. Serialportconfiguration.
Serial port:96,N,8,1
C. ProgrammingMode
Page Mode Printheadresolution
D. Printheadtestpattern.
E. AmountofmemoryavailablefortheImagebuffer.
Image buffer size:0245K F. Amountofmemoryusedandmemoryavailable
forFormstorage.
Fmem:0 (bytes)
G. Amountofmemoryusedandmemoryavailable
Gmem:0
forGraphicsstorage.
Emem:29600 H. Amountofmemoryusedandmemoryavailable
Available: 100959 forSoftfonts.
I8,0,001 rY JF WY I. TotalfreememoryavailableforForms,Fonts,
S4 D00 R0,0 ZT UN orGraphics
J. CurrentlyselectedCharacterSet(I)andImage
q832 Q1022,029
Buffermodesetting(r).
Option:d,Ff
rY=DoubleBufferingEnabled
oEw,x,y,z rN=DoubleBufferingDisabled
16 23 31 K. CurrentlyselectedPrintSpeed(S),
Cover: T=143, C=166 HeatDensity(D), ReferencePoint(R),
PrintOrientation(Z) and ErrorStatus(U).
L. CurrentlyselectedFormWidth(q)
Date:10-05-94
andLength(Q).
Time:01:00:00
M. CurrentHardwareandSoftwareOptionstatus.
N. CurrentAutoSenseThrough(Web/Gap)Sensor
Now inDUMPMODE values.Thethreenumbersrepresent;
PressFeedtoexit 1.BackingTransparentpoint
2.Setpoint
3.LabelTransparentpoint.
DumpModePrintSample O. HeadUp(Open)Sensorsettings
P. CurrentDateandTimesetinRealTimeClock.
Thessvalueswillonlybedisplayedifyourprinter
isequippedwiththeRealTimeClockfeature.
Q. CurrentDumpModeStatus.
14245L-001 Rev. A EPL Programming Guide 5/30/07

---

## Pagina 35

Printer Configuration 35
Determining Printer Firmware Version
The printer version numbers are a code used to document product function and the feature
support level of the printer. The latest firmware version and updates can be obtained from our
web site.
Programming Mode Configuration
Flash based printers are, by default, configured for Page (EPL2) mode operations. The
operator must convert the printer to Line Mode prior to the initial use of Line Mode. This is
done via a hardware select procedure with the Feed button during printer power-up. See the
OEPL1 command onpage132 for details on switching between line and page modes via
programming.
The following direct thermal printers support Line Mode (EPL1 emulation):
• LP2824
• LP2844
Note • The Line Mode (and Page Mode) configuration setting is retained after reset has been
issued or power has been cycled.
Manually Setting Line Mode
The Line Mode capable printer utilizes the Feed button during printer power-up to toggle
between the printer personality modes, Line and Page (EPL2).
1. With printer power off, press and hold the Feed button while turning on the printer, and
then release the button when the LED starts blinking red.
2. When the indicator LED starts flashing green, immediately press and hold the Feed
button.
3. Release the Feed button when the LED turns a steady amber (orange) color.
4. Verify printer personality with Dump Mode printout: Line Mode or Page Mode (EPL2).
5. Press the Feed button to exit the Dump Mode.
5/30/07 EPL Programming Guide 14245L-001 Rev. A

---

## Pagina 36

36 Printer Configuration
Media Detection
Media detection in EPL2 printers is a combination of programming and printer media sensing.
The Q (Set Form Length) and O (Option) commands program the media detection method.
The user must configure the printer for the media type and the (programmed) form or label in
use.
The printer can detect the beginning and end of the printable area on the media by one of three
methods: gap, notch (hole), or black line. The gap method detects the difference in optical
density of a label on a liner from the liner only with the transmissive (gap) sensor. The notch
method uses the transmissive sensor to detect a hole in the media (gap-less labels or tag stock).
The black line method uses the reflective sensor to detect a preprinted black line on the media
back (for gap-less labels or tag stock).
Printing on continuous media requires programming to control media positioning.
EPL2 printers also support a “Label Dispense” mode as a printer configuration option (for
most models). The printers use a “Label Taken” sensor to detect the removal of a label.
One or more of these sensors may require user adjustment or configuration for proper
operation. All EPL2 printers have an AutoSense feature to optimize label and label gap
detection by the transmissive (gap) sensor. See the printer’s user manual for printer specific
sensor adjustment control.
14245L-001 Rev. A EPL Programming Guide 5/30/07

---

## Pagina 37

3
EPL Commands
This section contains a complete alphabetical listing of all EPL commands.
Memory and Command Usage
The printer stores configuration settings, fonts, graphics (logos) and label form files into
printer memory. The Command Reference includes a Memory column with the following
terms:
• Image commands are use to assemble the printed label image in the print image buffer.
• Form commands are only used within forms.
• Session commands place the configuration setting in temporary RAM memory. The
setting reverts to the printer default when the printer has power cycled or is reset.
• Stored memory commands will check for differences with the existing command setting
prior to validating a memory write operation. The printer's non-volatile "flash" memory
has a limited number of write cycles (100,000 plus).
• Writes to memory commands are commands that do not check to see if a setting, file,
font, character or graphic is already present or if that condition is already set. These
commands are rarely used in forms except as a printer initialization and function oriented
configuration.
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |
| --- |
|  |


---

## Pagina 38

38 EPL Commands
Command Description Memory Page
Command Description Memory Page
Command Description Memory Page
A ASCII Text Image 41
AUTOFR Automatic Form Printing Form 48
B Bar Code Image 50
B RSS-14 Bar Code Image 56
b Aztec Image 60
Aztec Mesa Image 64
Data Matrix Image 66
MaxiCode Image 70
PDF417 Image 74
QR Code Image 81
C Counter Form 83
C Cut Immediate — 85
D Density Stored 86
dump Enable Dump Mode — 87
EI Print Soft Font Info. — 88
EK Delete Soft Font Writes 89
eR User Definable Error Response Writes 90
ES Store Soft Font Writes 91
f Cut Position Stored 96
fB Adjust Backup Position Writes 97
FE End Form Store Writes 98
FI Print Form Info. — 99
FK Delete Form Writes 100
FR Retrieve Form — 101
FS Store Form Writes 102
GG Retrieve Graphics Image 103
GI Print Graphics Info. — 104
GK Delete Graphic Writes 105
GM Store Graphic Writes 106
GW Direct Graphic Write Image 108
i Asian Character Spacing Stored 109
I Character Set Selection Stored 110
JB Disable Top Of Form Backup Stored 112
JC Disable Top Of Form Backup - All Cases Stored 113
14245L-001 Rev. A EPL Programming Guide 5/30/07

| Description | Memory |
| --- | --- |
| ASCII Text | Image |
| Automatic Form Printing | Form |
| Bar Code | Image |
| RSS-14 Bar Code | Image |
| Aztec | Image |
| Aztec Mesa | Image |
| Data Matrix | Image |
| MaxiCode | Image |
| PDF417 | Image |
| QR Code | Image |
| Counter | Form |
| Cut Immediate | — |
| Density | Stored |
| Enable Dump Mode | — |
| Print Soft Font Info. | — |
| Delete Soft Font | Writes |
| User Definable Error Response | Writes |
| Store Soft Font | Writes |
| Cut Position | Stored |
| Adjust Backup Position | Writes |
| End Form Store | Writes |
| Print Form Info. | — |
| Delete Form | Writes |
| Retrieve Form | — |
| Store Form | Writes |
| Retrieve Graphics | Image |
| Print Graphics Info. | — |
| Delete Graphic | Writes |
| Store Graphic | Writes |
| Direct Graphic Write | Image |
| Asian Character Spacing | Stored |
| Character Set Selection | Stored |
| Disable Top Of Form Backup | Stored |
| Disable Top Of Form Backup - All Cases | Stored |


---

## Pagina 39

EPL Commands 39
Command Description Memory Page
Command Description Memory Page
JF Enable Top Of Form Backup Stored 114
LE Line Draw Exclusive OR Image 115
LO Line Draw Black Image 116
LS Line Draw Diagonal Image 117
LW Line Draw White Image 118
M Memory Allocation Writes 119
N Clear Image Buffer Image 120
o Cancel Customized Settings Writes 121
oB Cancel Customize Bar Code Writes 122
oE Line Mode Font Substitution Writes 123
oH Macro PDF Offset Image 124
oM Disable Initial Esc Sequence Feed Stored 126
oR Character Substitution (Euro) Writes 127
oW Customize Bar Code Parameters Writes 128
O Options Select Stored 130
OEPL1 Set Line Mode Writes 132
P Print — 133
PA Print Automatic Form 134
q Set Form Width Stored 135
Q Set Form Length Stored 137
Transmissive (Gap) Sensor
Black Line Sensor|
Continuous Stock
r Set Double Buffer Mode Stored 140
R Set Reference Point Stored 141
S Speed Select Stored 142
T Define Date Layout (& Print Date) Writes 143
TS Set Real Time Clock Stored 144
TT Define Time Layout (& Print Time) Writes 145
U Print Configuration — 146
UA Enable Clear Label Counter Mode Session 147
UB Reset Label Counter Mode Writes 148
UE External Font Information Inquiry — 149
UF Form Information Inquiry — 150
UG Graphic Information Inquiry — 151
UI Host Prompts/Codepage Inquiry Session 152
5/30/07 EPL Programming Guide 14245L-001 Rev. A

| Description | Memory |
| --- | --- |
| Enable Top Of Form Backup | Stored |
| Line Draw Exclusive OR | Image |
| Line Draw Black | Image |
| Line Draw Diagonal | Image |
| Line Draw White | Image |
| Memory Allocation | Writes |
| Clear Image Buffer | Image |
| Cancel Customized Settings | Writes |
| Cancel Customize Bar Code | Writes |
| Line Mode Font Substitution | Writes |
| Macro PDF Offset | Image |
| Disable Initial Esc Sequence Feed | Stored |
| Character Substitution (Euro) | Writes |
| Customize Bar Code Parameters | Writes |
| Options Select | Stored |
| Set Line Mode | Writes |
| Print | — |
| Print Automatic | Form |
| Set Form Width | Stored |
| Set Form Length<br>Transmissive (Gap) Sensor<br>Black Line Sensor\|<br>Continuous Stock | Stored |
| Set Double Buffer Mode | Stored |
| Set Reference Point | Stored |
| Speed Select | Stored |
| Define Date Layout (& Print Date) | Writes |
| Set Real Time Clock | Stored |
| Define Time Layout (& Print Time) | Writes |
| Print Configuration | — |
| Enable Clear Label Counter Mode | Session |
| Reset Label Counter Mode | Writes |
| External Font Information Inquiry | — |
| Form Information Inquiry | — |
| Graphic Information Inquiry | — |
| Host Prompts/Codepage Inquiry | Session |


---

## Pagina 40

40 EPL Commands
Command Description Memory Page
Command Description Memory Page
UM Codepage & Memory Inquiry Session 153
UN Disable Error Reporting Stored 154
UP Codepage & Memory Inquiry/Print — 155
UQ Configuration Inquiry — 156
US Enable Error Reporting Stored 157
UT Enable Alternate Error Reporting Stored 159
U% Host Prompts/Battery Inquiry — 160
U$ Host Prompts/Motor Temperature Inquiry — 161
V Define Variable Form 162
W Windows Mode Stored 164
xa Sense Media Writes 165
X Box Draw Image 166
Y Serial Port Setup Stored 167
Z Print Direction Stored 168
? Download Variables Form 170
^@ Reset Printer — 171
^default Set Printer to Factory Defaults Writes 172
^ee Status Report - Immediate — 173
; Code Comment Line Form 174
14245L-001 Rev. A EPL Programming Guide 5/30/07

| Description | Memory |
| --- | --- |
| Codepage & Memory Inquiry | Session |
| Disable Error Reporting | Stored |
| Codepage & Memory Inquiry/Print | — |
| Configuration Inquiry | — |
| Enable Error Reporting | Stored |
| Enable Alternate Error Reporting | Stored |
| Host Prompts/Battery Inquiry | — |
| Host Prompts/Motor Temperature Inquiry | — |
| Define Variable | Form |
| Windows Mode | Stored |
| Sense Media | Writes |
| Box Draw | Image |
| Serial Port Setup | Stored |
| Print Direction | Stored |
| Download Variables | Form |
| Reset Printer | — |
| Set Printer to Factory Defaults | Writes |
| Status Report - Immediate | — |
| Code Comment Line | Form |


---

## Pagina 41

EPL Commands 41
A
A
ASCII Text
Description Renders an ASCII text string to the image print buffer. See Text (Fonts)
onpage26 for discussion on text handling in Page Mode programming.
Asian language EPL2 Page Mode printers have special firmware and printer (PCBA) memory
order options to support the large Asian character (ideogram) sets.
The Latin (English, etc.) font sets (1-5, a-z, and A-Z) are single-byte (8 bits per byte) ASCII
character maps. The Asian characters are double-byte mapped characters. The printed Asian
character is dependent on the double-byte ASCII values.
Syntax Ap ,p ,p ,p ,p ,p ,p ,”DATA”
1 2 3 4 5 6 7
Parameters This table identifies the parameters for this format:
Parameters Details
p = Horizontal start Horizontal start position (X) in dots.
1
position
p = Vertical start position Vertical start position (Y) in dots.
2
p = Rotation Characters are organized vertically from left to right and then
3
rotated to print.
Accepted Values:
0 = normal (no rotation)
1 = 90 degrees
2 = 180 degrees
3 = 270 degrees
Rotation for Asian Printers Only
Characters are organized horizontally from top to bottom and
then rotated to print. Asian printers support both horizontal
and vertical character rotation.
Accepted Values: (Asian Printers Only)
4 = normal (no rotation)
5 = 90 degrees
6 = 180 degrees
7 = 270 degrees
5/30/07 EPL Programming Guide 14245L-001 Rev. A

---

## Pagina 42

42 EPL Commands
A
Parameters Details
p = Font selection
4
Description
Value
203 dpi 300 dpi
1 20.3 cpi, 6 pts, (8 x 12 dots) 25 cpi, 4 pts, (12 x 20 dots)
2 16.9 cpi, 7 pts, (10 x 16 dots) 18.75 cpi, 6 pts, (16 x 28 dots)
3 14.5 cpi, 10 pts, (12 x 20 dots) 15 cpi, 8 pts, (20 x 36 dots)
4 12.7 cpi, 12 pts, (14 x 24 dots) 12.5 cpi, 10 pts, (24 x 44 dots)
5 5.6 cpi, 24 pts, (32 x 48 dots) 6.25 cpi, 21 pts, (48 x 80 dots)
A - Z Reserved for Soft Font storage.
a-z Reserved for printer driver support for storage of user-selected Soft
Fonts.
6 Numeric Only (14 x 19 dots) Numeric Only (14 x 19 dots)
7 Numeric Only (14 x 19 dots) Numeric Only (14 x 19 dots)
Asian Printers
8 Simplified Chinese, Japanese, Korean
203 dpi fonts : 24 x 24 dots
300 dpi Double-byte fonts: 36 x 36 dots
300 dpi Single-byte fonts: 24 x 26 dots
9 Traditional Chinese, Japanese
300 dpi Double-byte fonts: 36 x 36 dots
300 dpi Single-byte fonts: 24 x 26 dots
Korean - Reserved
• Fonts 1-5 are fixed pitch.
• Asian language option printers support a single language
with fonts 8 and 9.
p = Horizontal multiplier Horizontal multiplier expands the text horizontally.
5
Accepted Values: 1–6, 8
p = Vertical multiplier Vertical multiplier expands the text vertically.
6
Accepted Values: 1–9
p = Reverse image Accepted Values:
7
N = normal
R = reverse image
DATA = Fixed data field Fixed data field
The backslash (\) character designates the following character
is a literal and will encode into the data field.
To Print Enter into data field
" \"
“Company” \”Company\”
\ \\
\code\ \\code\\
14245L-001 Rev. A EPL Programming Guide 5/30/07

| 20.3 cpi, 6 pts, (8 x 12 dots) |
| --- |
| 16.9 cpi, 7 pts, (10 x 16 dots) |
| 14.5 cpi, 10 pts, (12 x 20 dots) |
| 12.7 cpi, 12 pts, (14 x 24 dots) |
| 5.6 cpi, 24 pts, (32 x 48 dots) |


| Numeric Only (14 x 19 dots) |
| --- |
| Numeric Only (14 x 19 dots) |


---

## Pagina 43

EPL Commands 43
A
Example • In this example, font 5 only supports upper case characters. Refer to Appendix B,
Character References, for a complete listing of available fonts and character sets supported.
N↵
A50,0,0,1,1,1,N,"Example 1"↵
A50,50,0,2,1,1,N,"Example 2"↵
A50,100,0,3,1,1,N,"Example 3"↵
A50,150,0,4,1,1,N,"Example 4"↵
A50,200,0,5,1,1,N,"EXAMPLE 5"↵
A50,300,0,3,2,2,R,"Example 6"↵
P1↵
The code above will produce this label:
Note • Use the LE command to create reverse print text instead of the “R” in the A command
parameter p . This is the recommend method because it provides the best size, position, and
7
centering of the black line (rectangle) bordering the reversed text.
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 44

44 EPL Commands
A
Special JIS Code Page Programming Considerations
The JIS keyboard technique utilizes double-byte data characters. Each byte of the of the
double-byte character can be represented by one of the lower 128 ASCII data characters (20
through 7F hexadecimal).
The printer uses a Shift-& to recognize a JIS character text data string. Once the JIS text mode
has been set with the Shift-&, the JIS text mode remains in effect until the text data string is
terminated.
Shift = 1C hexadecimal or 28 decimal
& (Ampersand) = 26 hexadecimal or 38 decimal
If any text data string contains the ASCII character values for the quote (”) character, then it
must be proceeded by a backslash.
” (quote) = 22 hexadecimal or 34 decimal
\ (backslash) = C5 hexadecimal or 92 decimal
Note • Enter the “Shift” character with the number pad on the standard 101 key PC
keyboard. With the Number Lock on, type 028 on the number pad while holding the ALT key
down. This is a standard method of entering ASCII characters not directly supported by a
keyboard.
When using the JIS, if the last character in a string of characters is a backslash (\), then the
Shift character followed by any character is required to exit the JIS character mode.
Example •
A50,0,0,8,1,1,N,"Example JIS <Shift>&5\<Shift>."
In this example:
• <Shift> represents the ASCII value 28 decimal.
• <Shift>& enters JIS text mode.
• <Shift>. exits JIS text mode
• (returns to Shift JIS text mode).
Variable Data and Counter Functions
The “Data” field can be replaced by or combined with the following commands:
• Vnn= Prints the contents of variable “nn” at this position where nn is a 2 digit number
from 00 to 99. For more information, refer to the V command onpage162.
• Cn= Prints the contents of counter “n” at this position where n is a one digit number from
0 to 9. For more information, refer to the C command onpage83.
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 45

EPL Commands 45
A
Example •
A50,0,0,1,1,1,N,"DATA"↵ : Writes Text
A50,50,0,2,1,1,N,V01↵ : Writes contents of variable
01
A50,100,0,3,1,1,N,C1↵ : Writes contents of counter 1
A50,100,0,3,1,1,N,C1+2↵ : Writes contents of counter 1
plus 2
Data with the RTC Time & Date Functions
The “Data” field can be replaced by or combined with the following variables:
• TT = Prints the current time at this position in the predefined format. See the TT
command for format selection. This variable is available only if the printer Time &
Date option is installed.
• TD = Prints the current date at this position in the predefined format. See the TD
command for format selection. This variable is available only if the printer Time &
Date option is installed.
Example •
A50,150,0,4,1,1,N,TT↵ : Writes current time
A50,200,0,5,1,1,N,TD↵ : Writes current date or a
combination of several
options:
A50,300,0,3,2,2,R,"Deluxe"V01C2"Combo"TDV01TT↵
: Writes the text “Deluxe”
followed by the contents of
variable 01 followed by the
contents of counter 2 followed
by the text “Combo” followed
by the current date followed
by the contents of variable 01
followed by the current time.
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


|  |  |
| --- | --- |
|  |  |


---

## Pagina 46

46 EPL Commands
A
A
Simple Expressions in Data Fields
An advanced function of the A command allows addition and subtraction to be
performed on constant and variable values in flash printers.
Syntax Ap ,p ,p ,p ,p ,p ,p ,”DATA”[p p p …]
1 2 3 4 5 6 7 8 9 10
Parameters This table identifies the parameters for this format:
Parameters Details
p through p ”DATA” See the first page of the A command onpage41.
1 7,
p = Required variable Must be a variable data field number, such as V00, V01, and
8
data field number so forth.
p = Required operator Accepted Values: + or –
9
p = Required Variable data field number or constant value.
10
Accepted Values:
Constant = 0 to 2147483647
Variable = 0 to 2147483647
Result = -2147483648 to 2147483647
• The expression must start with a variable field.
• The character field length defined for the first variable in
the expression will be used to format the result. If the
result is of a greater length than the defined character
length, then the result field will contain ‘X’s.
• A syntax error will be generated during form storage if the
constant value is too large.
• If an error occurs during the evaluation of the expression,
the resultant field will be filled with ‘X’s.
14245L-001 Rev. A EPL Programming Guide 5/30/07

---

## Pagina 47

EPL Commands 47
A
Example •
FK"1"↵
FK"1"↵
FS"1"↵
V00,10,N,"Enter current mileage"↵
A100,100,0,4,1,1,N,"Current mileage is “V00" miles.”↵
A100,200,0,4,1,1,N,"Change oil at “V00+3000" miles.”↵
FE↵
↵
FK"2"↵
FK"2"↵
FS"2"↵
V00,10,N,"Enter current mileage."↵
V01,10,N,"Enter interval mileage."↵
A100,100,0,4,1,1,N,"Current mileage is “V00" miles.”↵
A100,200,0,4,1,1,N,"Mileage interval is “V01" miles.”↵
A100,200,0,4,1,1,N,"Change oil at “V00+V01" miles.”↵
FE↵
↵
FK"3"↵
FK"3"↵
FS"3"↵
V00,10,N,"Enter value 1."↵
V01,10,N,"Enter value 2."↵
V02,10,N,"Enter value 3."↵
A100,200,0,4,1,1,N,"Answer: “V01+123+V00-10-V02↵
FE↵
Asian Character Font Sets
Asian language support is an optional feature and requires a special version of the printer
(PCBA) to support the large Asian character sets.
The Asian character maps and special features of the A command that support the
character sets can be found starting on page29.
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 48

48 EPL Commands
AUTOFR
AUTOFR
Automatic Form Printing
Description This special form process allows you to detach the printer from the computer
and print in a standalone mode. The EPL2 printer reserves the form name AUTOFR to allow
the printer to automatically start a form when the printer is initialized power-up. This feature
can be used in many ways, including the following:
• Feed a single label in peel mode and print multiple labels set to the number of labels on the
roll.
• Have a form with a variable and enter the variable with a scanner, terminal, weight scale,
circuit analyzer or any other device capable of sending ASCII character data.
Mobile printers, such as the TR 220, ignore this command.
Syntax FK"AUTOFR" or FS"AUTOFR"
Parameters There are no parameters for this command.
Example • Download a form to the printer with the name AUTOFR.
↵ : Line Feed to initialize the
printer
FK"AUTOFR"↵ : Form Kill (delete any existing
AUTOFR)
FS"AUTOFR"↵ : Form Save (save file from here
to FE at the bottom)
V00,8,L,""↵ : Variable field definition
Q254,20↵ : Label height followed by gap
width
S2↵ : Speed (2ips)
D7↵ : Density setting
ZB↵ : Print direction (ZT flips it
180 degrees)
A340,20,0,4,1,2,N,"QUANTITY"↵: Fixed text line
B265,75,0,3,2,4,101,B,V00↵ : Bar code definition
PA1↵ : Print 1 label Automatically *
FE↵ : Form End (Line Feed)
Note • AUTOFR treats any incoming data as a variable intended for printing. If you send the
printer a memory partition command, the label will print, and if you send a delete command,
the label will also print. So, while you are testing AUTOFR it is best to use another name for
the form. Once you are satisfied with the form, rename it AUTOFR before you download it.
There is no need to specify a file extension.
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 49

EPL Commands 49
AUTOFR
Isolating Data from the Input Device
Place the printer in the diagnostic dump mode and send from your data input device.
• All characters the device sends will be printed on the label.
• If nothing prints, nothing is arriving; check pin-outs and serial settings.
Disabling AUTOFR
Send a XOFF data character (13 hex. or ASCII 19) or a NUL(00 hex. or ASCII 0) to the
printer. The form may now be deleted from the printer.
Removing AUTOFR
The programmer must send a Delete Form - FK command to the printer after disabling
AUTOFR.
FK"AUTOFR"↵
FK"AUTOFR"↵
5/30/07 EPL Programming Guide 14245L-001 Rev. A

---

## Pagina 50

50 EPL Commands
B
B
Bar Code
Description Use this command to print standard bar codes.
Syntax Bp ,p ,p ,p ,p ,p ,p ,p ,”DATA”
1 2 3 4 5 6 7 8
Parameters This table identifies the parameters for this format:
Parameters Details
p = Horizontal start Horizontal start position (X) in dots.
1
position
p = Vertical start position Vertical start position (Y) in dots.
2
p = Rotation Accepted Values:
3
0 = normal (no rotation)
1 = 90 degrees
2 = 180 degrees
3 = 270 degrees
p = Bar Code selection. See Table1, Bar Codes onpage51 for more information.
4
p = Narrow bar width Narrow bar width in dots.
5
See Table1, Bar Codes onpage51 for more information.
p = Wide bar width Wide bar width in dots.
6
Accepted Values: 2-30
See Table1, Bar Codes onpage51 for more information.
p = Bar code height Bar code height in dots.
7
p = Print human readable Accepted Values:
8
code B = yes
N = no
DATA = Fixed data field The data in this field must comply with the selected bar code’s
specified format. The backslash (\) character designates the
following character is a literal and will encode into the data
field. Refer to the following examples:
To Print Enter into data field
" \"
“Company” \”Company\”
\ \\
\code\ \\code\\
14245L-001 Rev. A EPL Programming Guide 5/30/07

---

## Pagina 51

EPL Commands 51
B
Table 1 • Bar Codes
P4 P5 P6
Description
Value Value Value
Code 39 std. or extended 3 1-10 Y
Code 39 with check digit 3C 1-10 N
Code 93 9 1-10 N
Code 128 UCC Serial Shipping Container Code 0 1-10 N
Code 128 auto A, B, C modes 1 1-10 N
Code 128 mode A 1A 1-10 N
Code 128 mode B 1B 1-10 N
Code 128 mode C 1C 1-10 N
Code 128 with Deutsche Post check digit 1D 2-10 N
Codabar K 1-10 Y
EAN8 E80 2-4 N
EAN8 2 digit add-on E82 2-4 N
EAN8 5 digit add-on E85 2-4 N
EAN13 E30 2-4 N
EAN13 2 digit add-on E32 2-4 N
EAN13 5 digit add-on E35 2-4 N
German Post Code 2G 3-4 N
Interleaved 2 of 5 2 1-10 Y
Interleaved 2 of 5 with mod 10 check digit 2C 1-10 Y
Interleaved 2 of 5 with human readable check digit 2D 1-10 Y
Postnet 5, 9, 11 & 13 digit P — N
Planet 11 & 13 digit PL — N
Japanese Postnet J — —
UCC/EAN 128 1E 1-10 N
UPC A UA0 2-4 N
UPC A 2 digit add-on UA2 2-4 N
UPC A 5 digit add-on UA5 2-4 N
UPC E UE0 2-4 N
UPC E 2 digit add-on UE2 2-4 N
UPC E 5 digit add-on UE5 2-4 N
UPC Interleaved 2 of 5 2U 1-10 Y
Plessey (MSI-1) with mod. 10 check digit L — —
MSI-3 with mod. 10 check digit M — —
5/30/07 EPL Programming Guide 14245L-001 Rev. A

| P4<br>Value | P5<br>Value |
| --- | --- |
| 3 | 1-10 |
| 3C | 1-10 |
| 9 | 1-10 |
| 0 | 1-10 |
| 1 | 1-10 |
| 1A | 1-10 |
| 1B | 1-10 |
| 1C | 1-10 |
| 1D | 2-10 |
| K | 1-10 |
| E80 | 2-4 |
| E82 | 2-4 |
| E85 | 2-4 |
| E30 | 2-4 |
| E32 | 2-4 |
| E35 | 2-4 |
| 2G | 3-4 |
| 2 | 1-10 |
| 2C | 1-10 |
| 2D | 1-10 |
| P | — |
| PL | — |
| J | — |
| 1E | 1-10 |
| UA0 | 2-4 |
| UA2 | 2-4 |
| UA5 | 2-4 |
| UE0 | 2-4 |
| UE2 | 2-4 |
| UE5 | 2-4 |
| 2U | 1-10 |
| L | — |
| M | — |


---

## Pagina 52

52 EPL Commands
B
Bar Code Table Notes
1. Hyphens maybe used in data as a data separator and will be ignored.
2. Use ASCII 06 to delimit variable length fields.
3. Japanese Postal Code accepts alpha-numeric characters. It truncates the data after 20
characters, and pads up to 20 with a pad character.
4. The data for a Deutsche Post Code 128 barcode consists of 12 characters:
BBNNNNNNNNDE, where B is any character in the ‘B’ character set, N is any decimal
digit (‘0’ – ‘9’), and DE are the literal characters “DE” (Germany). A 1D bar code type
creates a standard Code 128 symbol, but the firmware calculates and inserts a check digit
between the last N and the DE before rendering the bar code. It will abort and report a
syntax error if any of the 8 characters between AA and DE are not digits. It will, however,
allow any number of function codes F1 through F3 to be interspersed with the digits.
5. Planet: See USPS Publication 197 for details. Either 11 or 13 digits may be supplied, and
the printer calculates and appends a check digit for a total of 12 or 14 digits. As with
Postnet, hyphens (‘-’) may be used as data separators for readability and will be discarded
by the printer.
Example 1 •
B10,10,0,PL,5,5,5,N,"12-34567-890123"
Example 2 •
N↵
B10,10,0,3,3,7,200,B,"998152-001"↵
P1↵
produces this label:
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


|  |  |
| --- | --- |
|  |  |


---

## Pagina 53

EPL Commands 53
B
Bar Codes with Variables & Counters
The data field can be replaced by or combined with the following commands:
• Vnn = Prints the contents of variable “nn” at this position.
Range of nn = 00 to 99.
• Cn = Prints the contents of counter “n” at this position.
Range of n = 0 to 9
See Appendix C for additional Data parameters for printers with the RTC (real time clock)
option installed.
Example •
B50,0,0,3,1,2,50,B,"DATA"↵ : Writes bar code
B50,50,0,3,1,2,50,N,V01↵ : Writes contents of variable 01
as bar code
B50,50,0,3,1,2,50,N,C1↵ : Writes contents of counter 1
as bar code
B50,50,0,3,1,2,50,N,C1+2↵ : Writes contents of counter 1
plus2 as bar code or a
combination of several
options:
B50,300,0,3,1,2,50,B,"Deluxe"V01C2"Combo"V01↵
: Writes the text “Deluxe”
followed by the contents of
variable 01 followed by the
contents of counter 2 followed
by the text “Combo” followed
by the contents of variable 01
all as a code 39 bar code.
or a combination of several options:
B50,300,0,3,1,2,50,B,"Deluxe"V01C2"Combo"V01↵
: Writes the text “Deluxe”
followed by the contents of
variable 01 followed by the
contents of counter 2 followed
by the text “Combo” followed
by the contents of variable 01
all as a code 39 bar code.
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 54

54 EPL Commands
B
Data with the RTC Time & Date Functions
The “Data” field can be replaced by or combined with the following variables:
• TT = Prints the current time at this position in the predefined format. See the TT command
for format selection. This variable is available only if the printer Time & Date option is
installed.
• TD = Prints the current date at this position in the predefined format. See the TD
command for format selection. This variable is available only if the printer Time & Date
option is installed.
Note • Some bar code formats will not support date names or the date or time delimiters used
by the printer to separate data parameters.
Code 128 Bar Code Function Characters
The printer supports Code 128 function control characters (FCN#). Multiple FCN#s, TTs, TDs
and “DATA” strings can be concatenated, allowing them to be inserted anywhere within the
symbol.
FCN2, FCN3 and FCN4 are illegal in Code 128 mode C (p4 = 1C) and will result in a syntax
error.
Please refer to the Code 128 standard for a description of function characters FNC1 through
FNC3.
“Standard” Code 128 can encode all 128 standard ASCII characters (0 – 127). Function
character FCN4 provides a means of also encoding extended ASCII characters (128 – 255). It
directs the reader to add 128 to the value of each affected character before transmitting it.
Two consecutive FCN4s toggle between standard and extended ASCII mode for all
succeeding data characters (until the end of the symbol, or until another pair of FCN4s is
encountered). This is referred to as latching into extended ASCII mode or latching into
standard ASCII mode.
A single FCN4 toggles between standard and extended ASCII mode for only a single
following data character. This is referred to as shifting into extended ASCII mode or shifting
into standard ASCII mode.
Both code sets A and B are needed to represent the entire extended ASCII character set, just as
both sets are needed to represent the standard ASCII character set.
FCN4s can be inserted manually, if necessary or desired, by following the syntax described
above. The printer will, however, insert them automatically if extended ASCII characters are
encountered in the DATA. It will do so in the most efficient manner possible:
• If up to 4 contiguous extended ASCII characters are encountered, it will shift into
extended ASCII mode by inserting a single FCN4 before each one;
• If 5 or more contiguous extended ASCII characters are encountered, it will latch into
extended ASCII mode by inserting two FCN4s before them.
• While latched into extended ASCII mode, it will apply the same rules if standard ASCII
characters are encountered.
14245L-001 Rev. A EPL Programming Guide 5/30/07

---

## Pagina 55

EPL Commands 55
B
Thus, the preferred way to encode extended ASCII characters is to simply embed them in the
DATA and let the printer manage the encoding task. For best results, the code set should also
not be specified (i.e., p4=1). FCN4 s should be manually inserted only in systems where
extended ASCII characters cannot be transmitted to the printer.
Important • It is illegal to mix automatic and manual modes within the data for a single
symbol; i.e., an extended ASCII character encountered in the data after an will be considered
a syntax error. Likewise, an FCN4 after an extended ASCII character will also be considered
a syntax error.
5/30/07 EPL Programming Guide 14245L-001 Rev. A

---

## Pagina 56

56 EPL Commands
B
B
RSS-14 Bar Code Specific Options
Description Use this command to print RSS-14 bar code family bar codes for numeric data.
The printer supports a subset of the RSS bar code family set. The subset includes basic RSS-
14, RSS Limited, RSS Stacked and RSS Truncated. The printer does not support RSS
Expanded or two dimensional composite bar codes.
Printer Models: 3842 and 2844*
* - Available as a firmware download from the www.zebra.com website.
Syntax Bp ,p ,p ,p ,p ,p ,p ,p ,”DATA”
1 2 3 4 5 6 7 8
Parameters This table identifies the parameters for this format:
Parameters Details
p = Horizontal start Horizontal start position (X) in dots.
1
position
p = Vertical start position Vertical start position (Y) in dots.
2
p = Rotation Accepted Values:
3
0 = normal (no rotation)
1 = 90 degrees
2 = 180 degrees
3 = 270 degrees
p = RSS-14 Bar Code
4
selection Value Description Width Min. Height
Multiplier Multiplier
R14 Basic RSS-14 96 33
RL Limited 74 10
RS Stacked 50 13
RT Truncated 96 13
p = Narrow bar width Narrow bar width in dots.
5
Accepted Values: 1-10
The narrowest module will be this number of dots.
Symbol Width is the value of p times the applicable bar code
5
Width Multiplier listed in the table for the selected RSS-14
bar code type (p4). This overall symbol width value includes
the required symbol "quite zone".
p = Wide bar width Wide bar width in dots.
6
Accepted Values: 2
p = Bar code height Bar code height in dots.
7
14245L-001 Rev. A EPL Programming Guide 5/30/07

| Description | Width<br>Multiplier |
| --- | --- |
| Basic RSS-14 | 96 |
| Limited | 74 |
| Stacked | 50 |
| Truncated | 96 |


---

## Pagina 57

EPL Commands 57
B
Parameters Details
p = Print human readable Accepted Values:
8
code. B = yes
N = no
DATA = Fixed data field. The data in this field must comply with the selected bar code’s
specified format. The backslash (\) character designates the
following character is a literal and will encode into the data
field. Refer to the following examples:
Value Description Max. Numeric Value
R14 Basic RSS-14 9999999999999
RL Limited 1999999999999
RS Stacked 9999999999999
RT Truncated 9999999999999
5/30/07 EPL Programming Guide 14245L-001 Rev. A

| Description |
| --- |
| Basic RSS-14 |
| Limited |
| Stacked |
| Truncated |


---

## Pagina 58

58 EPL Commands
B
Data with the RTC Time & Date Functions
The “Data” field can be replaced by or combined with the following variables:
• TT = Prints the current time at this position in the predefined format. See the TT command
for format selection. This variable is available only if the printer RTC Time & Date option
is installed.
• TD = Prints the current date at this position in the predefined format. See the TD
command for format selection. This variable is available only if the printer RTC Time &
Date option is installed.
Because the RSS-14 bar code symbols only support numeric data, the time and date data
recalled by the TD and TT commands must not include delimiters, i.e. "/,-, or :" or any other
delimiters
Example 1 •
N↵
B100,100,0,RL,4,4,40,B,"1234567890"↵
B100,300,0,R14,4,4,40,B,"1234567890"↵
B100,500,0,RS,4,4,52,B,"1234567890"↵
B100,700,0,RL,4,4,40,B,"9876543210"↵
P↵
will produce this label:
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 59

EPL Commands 59
B
Example 2 •
N↵
B100,100,0,RL,4,4,40,B,"1234567890"↵
B400,300,0,RT,4,4,40,B,"1234567890"↵
B500,900,0,RS,4,4,54,B,"1234567890"↵
B500,900,1,RS,4,4,54,B,"1234567890"↵
B500,900,2,RS,4,4,54,B,"1234567890"↵
B500,900,3,RS,4,4,54,B,"1234567890"↵
B100,1350,0,RL,4,4,40,B,"9876543210"↵
P↵
will produce this label:
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 60

60 EPL Commands
b
b
2D Bar Code – Aztec Specific Options
Description Use this command to print an Aztec two dimensional bar code symbol. The
symbols are square on a square grid with a square central bullseye finder. Data is encoded in a
series of “layers” that circle around the bullseye pattern. Each additional layer completely
surrounds the previous layer thus causing the symbol to grow in size as more data is encoded.
Supported Printer Models: 2844
Available as a firmware download from the www.zebra.com website.
Syntax bp ,p ,p ,[p ,][p ,][p ,][p ,][p ,]”DATA”
1 2 3 4 5 6 7 8
Parameters This table identifies the parameters for this format:
Parameters Details
p = Horizontal start Horizontal start position (X) in dots.
1
position
p = Vertical start position Vertical start position (Y) in dots.
2
p = A - Selects Aztec bar —
3
code
Order is not important for parameters p -p . Include the prefix letter (d,e,f,m or r) to select
4 8
the parameter followed by a valid numeric value.
p (d)= Symbol Scaling Default Value: 3
4
Accepted Values: 1–55
p (e) = Symbol layer Both layer and error correction effect the symbol size.
5
and/or error Accepted Values:
correction levels.
e0 = Default checkword level (23% +3)
e1–e99 = 1% to 99% check words
e101–e104 = Compact symbol with 1 to 4 layers
e201–e232 = Full symbol with 1 to 32 layers
e300 = Rune symbol
p (f) = Enables the Enables the flg(n) format using the ASCII Escape
6
flg(n) format character (27 decimal).
Default Value: Disabled
p (m) = Enables menu Default Value: Disabled
7
support option
p (r) = Selects an Selects an inverse image of the bar code (sometimes known as
8
inverse image of the reverse video or a negative image).
bar code
"DATA"=ASCII data or Any combination of data strings, time fields (TT), date fields
Binary data bytes (TD), variables and/or counters (the latter two within forms
only) that resolve to a valid string of characters.
14245L-001 Rev. A EPL Programming Guide 5/30/07

---

## Pagina 61

EPL Commands 61
b
Within literal strings all ASCII characters 0–255 dec. (00-FF hex.) are allowed. Quotes (ASCII
34d) and backslashes (ASCII 92d) must be uniquely handled.
The backslash (\) character designates the following character is a literal and will encode into
the data field. Refer to the following examples:
To Print Enter into data field
" \"
“Company” \“Company\”
\ \\
\code\ \\code\\
ø \↵
Within forms, further limitations include that a NULL (ASCII 0) character may not appear
within bar code DATA and entry limitations do not provide for a way to enter linefeed
characters into variables.
The programmer should rely on the symbology’s specification to insure format compliance
and proper implementation. See the AIM web site for specifications at:
http://www.aimglobal.org/
Example 1 • This Aztec Bar Code example uses default values for all parameters.
b0,0,A,"0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"
Example 2 • This Aztec Bar Code example specifies an element size of 9 (triple the default).
b0,0,A,d9,"0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


|  |  |
| --- | --- |
|  |  |


---

## Pagina 62

62 EPL Commands
b
Example 3 • This Aztec Bar Code example specifies a checkword level of 98%.
b0,0,A,e98,"0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"
Example 4 • This Aztec Bar Code example specifies a compact symbol with 4 layers.
b0,0,A,e104,"0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"
Example 5 • This Aztec Bar Code example specifies a full symbol with 9 layers.
b0,0,A,e209,"0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"
Example 6 • This Aztec Bar Code example specifies a Rune symbol.
b0,0,A,e300,"0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


|  |  |
| --- | --- |
|  |  |


|  |  |
| --- | --- |
|  |  |


|  |  |
| --- | --- |
|  |  |


| Example 6 • This Aztec Bar Code example specifies a Rune symbol. |
| --- |
| b0,0,A,e300,"0123456789ABCDEFGHIJKLMNOPQRST |
|  |
| v. A EPL Programming Guide |
|  |
|  |


---

## Pagina 63

EPL Commands 63
b
Example 7 • This Aztec Bar Code example specifies flg(n) format using ASCII 27 escape
character.
b0,0,A,f,"0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"
Example 8 • This Aztec Bar Code example specifies a menuing symbol.
b0,0,A,m,"0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"
u gsy bo.
Example 9 • This Aztec Bar Code example specifies reverse printing (white on black).
b0,0,A,r,"0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"
se printing (wh
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |  |
| --- | --- | --- |
|  |  |  |
| Example 7 • This Aztec Bar Code example specifies flg(n) format usi |  |  |
| character. |  |  |
| b0,0,A,f,"0123456789ABCDEFGHIJKLMNOPQRSTUVW |  |  |
|  |  |  |
|  |  |  |
|  |  |  |
|  |  |  |
| Example 8 • This Aztec Bar Code example specifies a menuing symbo |  |  |
| b0,0,A,m,"0123456789ABCDEFGHIJKLMNOPQRSTUVW<br>u gsy bo. |  |  |
|  |  |  |
|  |  |  |
|  |  |  |
| Example 9 • This Aztec Bar Code example specifies reverse printing ( |  |  |
| b0,0,A,r,"0123456789ABCDEFGHIJKLMNOPQRSTUVW |  |  |
| se printing (wh |  |  |
|  |  |  |
|  |  |  |


|  |  |
| --- | --- |
|  |  |


|  |  |
| --- | --- |
|  |  |


---

## Pagina 64

64 EPL Commands
b
b
2D Bar Code – Aztec Mesa Specific Options
Description Use this command to print an Aztec Mesa composite bar code symbol (a
standard linear bar code and a modified Aztec two dimensional bar as a single symbol. The
two dimensional data is encoded in a series of “layers” lying above and in some cases
continuing below the adjacent standard bar code symbol.
Printer Models: 2844
Available as a firmware download from the www.zebra.com website.
Syntax bp ,p ,p ,p ,[p ,][p ,][p ,][p ,][p ,][p ,][p ,]”DATA
1 2 3 4 5 6 7 8 9 10 11
Parameters This table identifies the parameters for this format:
Parameters Details
p = Horizontal start Horizontal start position (X) in dots.
1
position
p = Vertical start position Vertical start position (Y) in dots.
2
p = AZ - Selects Aztec —
3
bar code
Order is not important for parameters p -p . Include the prefix letter (d,e,f,m or r) to select
4 8
the parameter followed by a valid numeric value.
p (d)= Standard linear Use the B command parameters to populate this parameter.
4
bar code The B command's horizontal and vertical start symbol
position parameters p and p are not used.
1 2
This p parameter is terminated with the a Z immediately
4
following the standard bar codes DATA parameter 'close
quote' (").
Example • This is an example of a standard bar code:
B50,0,0,3,1,2,50,B,"1234567890abc"↵
Example • This is an example of p portion of the standard
4
bar code of the same bar code symbol.
0,3,1,2,50,B,"1234567890abc"Z
p (e) = Symbol layer Both layer and error correction effect the symbol size.
5
and/or error Accepted Values:
correction levels.
e0 = Default checkword level (23% +3)
e1–e99 = 1% to 99% check words
e101–e104 = Compact symbol with 1 to 4 layers
e201–e232 = Full symbol with 1 to 32 layers
p (f) = Enables the Enables the flg(n) format using the ASCII Escape
6
flg(n) format character (27 decimal).
Default Value: Disabled
14245L-001 Rev. A EPL Programming Guide 5/30/07

---

## Pagina 65

EPL Commands 65
b
Parameters Details
p (m) = Enables menu Default Value: Disabled
7
support option
p (r) = Selects an Selects an inverse image of the bar code (sometimes known as
8
inverse image of the reverse video or a negative image).
bar code
p (b) = Intra Symbol Specifies the width of the barrier in narrow bar widths
9
Barrier Width between the Aztec and standard linear bar code areas.
Accepted Values: 1–9
Default Value:
4 for EAN 13 and UPC A,
2 for all other supported standard linear bar codes
p (s) = Symbol Must preceed the p (c)parameter.
10 11
Symmetry Default Value: Regular (stacked)
Value: e (even symmetry)
p (c) = Selects an Enables the flg(n) format using the ASCII Escape
11
inverse image of the character (27 decimal).
bar code Default Value: Disabled
"DATA"=ASCII data or Any combination of data strings, time fields (TT), date fields
Binary data bytes (TD), variables and/or counters (the latter two within forms
only) that resolve to a valid string of characters.
Within literal strings all ASCII characters 0–255 dec. (00-FF hex.) are allowed. Quotes (ASCII
34d) and backslashes (ASCII 92d) must be uniquely handled.
The backslash (\) character designates the following character is a literal and will encode into
the data field. Refer to the following examples:
To Print Enter into data field
" \"
“Company” \“Company\”
\ \\
\code\ \\code\\
ø \↵
Within forms, further limitations include that a NULL (ASCII 0) character may not appear
within bar code DATA and entry limitations do not provide for a way to enter linefeed
characters into variables.
The programmer should rely on the symbology’s specification to insure format compliance
and proper implementation. See the AIM web site for specifications at:
http://www.aimglobal.org/
5/30/07 EPL Programming Guide 14245L-001 Rev. A

---

## Pagina 66

66 EPL Commands
b
b
2D Bar Code – Data Matrix Specific Options
Description Use this command to print Data Matrix two dimensional bar code symbols.
The printer will automatically interpret and encode data into Data Matrix bar code symbols,
using the ECC 200 data quality format. Individual module size, columns and rows parameters
can be specified or the printer can automatically calculate and set one or all of these
parameters.
The symbol is made of square modules arranged within a rectangular shape which includes a
perimeter scan recognition pattern. The scan recognition pattern produced by the EPL2 printer
also includes a "quiet zone" one module wide on all outside edges of the bar code to ensure
data integrity.
Printer Models: 3842 and 2844*
* Available as a firmware download from the www.zebra.com website.
Syntax bp ,p ,p ,[,p ,][,p ][,p ][,p ],”DATA”
1 2 3 4 5 6 7
Parameters This table identifies the parameters for this format:
Parameters Details
p = Horizontal start Horizontal start position (X) in dots.
1
position
p = Vertical start position Vertical start position (Y) in dots.
2
p = D - Selects Data —
3
Matrix bar code
Order is not important for parameters p -p . Include the prefix letter (c,r,h, or v) to
4 7
select the parameter followed by a valid numeric value.
p (c)= Number of See the Symbol Geometries table for valid column values.
4
columns to encode
p (r) = Number of rows See the Symbol Geometries table for valid row values.
5
to encode
p (h) = Enables the Sets the minimum square data module size used for encoding
6
flg(n) format data.
Accepted Values: 1-40
Default Value: 5
p (v) = Selects an Selects an inverse image of the bar code (sometimes known as
7
inverse image of the reverse video or a negative image).
bar code
"DATA"=ASCII data or Any combination of data strings, time fields (TT), date fields
Binary data bytes (TD), variables and/or counters (the latter two within forms
only) that resolve to a valid string of characters.
Within literal strings all ASCII characters 0–255 dec. (00-FF hex.) are allowed. Quotes (ASCII
34d) and backslashes (ASCII 92d) must be uniquely handled.
14245L-001 Rev. A EPL Programming Guide 5/30/07

---

## Pagina 67

EPL Commands 67
b
The backslash (\) character designates the following character is a literal and will encode into
the data field. Refer to the following examples:
To Print Enter into data field
" \"
“Company” \“Company\”
\ \\
\code\ \\code\\
ø \↵
Within forms, further limitations include that a NULL (ASCII 0) character may not appear
within bar code DATA and entry limitations do not provide for a way to enter linefeed
characters into variables.
The programmer should rely on the symbology’s specification to insure format compliance
and proper implementation. See the AIM web site for specifications at:
http://www.aimglobal.org/
Table 2 • Data Matrix Symbol Geometries
Numeric Alpha-numeric Compressed
Rows Columns
Capacity Capacity data size
.. _ _
8 18 10 6 5
8 32 20 13 10
10 10 6 3 3
12 12 10 6 5
12 26 32 22 16
12 36 44 31 22
14 14 16 10 8
16 16 24 16 12
16 36 64 46 32
16 48 98 72 49
18 18 36 25 18
20 20 44 31 22
22 22 60 43 30
24 24 72 52 36
26 26 88 64 44
1. Paired digits represent the best-case compression. Adjacent digit pairs can be encoded
into eight bits.
2. The implied cmopressibility requires the data be composed of either all lowercase or all
uppercase characters with digits and spaces allowed.
3. Any ASCII character (0–127) can be encoded one-to-one, extended ASCII (128-255)
requires an additional two-byte overhead.
5/30/07 EPL Programming Guide 14245L-001 Rev. A

| Columns | Numeric<br>Capacity<br>.. | Alpha-numeric<br>Capacity<br>_ |
| --- | --- | --- |
| 18 | 10 | 6 |
| 32 | 20 | 13 |
| 10 | 6 | 3 |
| 12 | 10 | 6 |
| 26 | 32 | 22 |
| 36 | 44 | 31 |
| 14 | 16 | 10 |
| 16 | 24 | 16 |
| 36 | 64 | 46 |
| 48 | 98 | 72 |
| 18 | 36 | 25 |
| 20 | 44 | 31 |
| 22 | 60 | 43 |
| 24 | 72 | 52 |
| 26 | 88 | 64 |


---

## Pagina 68

68 EPL Commands
b
Table 2 • Data Matrix Symbol Geometries
Numeric Alpha-numeric Compressed
Rows Columns
Capacity Capacity data size
.. _ _
32 32 124 91 62
36 36 172 127 86
40 40 228 169 114
44 44 288 214 144
48 48 348 259 174
52 52 408 304 204
64 64 560 418 280
72 72 736 550 368
80 80 912 682 456
88 88 1152 862 576
96 96 1392 1042 696
104 104 1632 1222 816
120 120 2100 1573 1050
132 132 2608 1954 1304
144 144 3116 2335 1558
1. Paired digits represent the best-case compression. Adjacent digit pairs can be encoded
into eight bits.
2. The implied cmopressibility requires the data be composed of either all lowercase or all
uppercase characters with digits and spaces allowed.
3. Any ASCII character (0–127) can be encoded one-to-one, extended ASCII (128-255)
requires an additional two-byte overhead.
Automatic Data Matrix Bar Code Generation
The printer automatically tests and changes the Data Matrix bar code geometry to optimize the
symbol size per the specified column and row (p - thec prefix and p - the r prefix)
4 5
parameters. If no row or column parameters are specified, the printer will create a minimum
size symbol based on the calculated compressed size of the data. The number of columns and
rows in the symbol will be automatically determined with a preference towards the smallest
square symbol that will accommodate the compressed size.
The user may wish to force the number of rows and/or columns to a larger value to achieve
uniform symbol sizes.
14245L-001 Rev. A EPL Programming Guide 5/30/07

| Columns | Numeric<br>Capacity<br>.. | Alpha-numeric<br>Capacity<br>_ |
| --- | --- | --- |
| 32 | 124 | 91 |
| 36 | 172 | 127 |
| 40 | 228 | 169 |
| 44 | 288 | 214 |
| 48 | 348 | 259 |
| 52 | 408 | 304 |
| 64 | 560 | 418 |
| 72 | 736 | 550 |
| 80 | 912 | 682 |
| 88 | 1152 | 862 |
| 96 | 1392 | 1042 |
| 104 | 1632 | 1222 |
| 120 | 2100 | 1573 |
| 132 | 2608 | 1954 |
| 144 | 3116 | 2335 |


---

## Pagina 69

EPL Commands 69
b
If the column (c)parameter is only specified and it's “18”, “26”, “32”, “36”, or “48”, or if only
rows is specified and it is “8”, “12”, or “16”, a rectangular symbol may be produced. For
example, if “,r12” is specified (with no column parameter), then sizes 12x12, 12x26, and
12x36 are possible selections. The alphanumeric capacity of those symbols is 6, 22, or 31
characters respectively. The smallest symbol size that will accommodate the data will be
created. If rows, cols, and/or data length are not compatible with a symbol from the Symbol
Geometries table, an error 03 (Data Length Error) will be reported, and no symbol will be
produced.
Example •
N↵
b30,20,D,h8,"Zebra Technologies corporation is the
leading worldwide manufacturer of bar code labeling
solutions and a leading provider of instant-issuance
plastic card printers. We distribute our on-demand bar
code label printers, plastic card printers, secure ID
printing systems, software and related supplies under the
Zebra and Eltron brand names to users in more than 90
countries. Our products are used in high-growth automatic
identification applications that improve quality and
productivity. We count among our customers more than 70
percent of the FORTUNE 500."↵
will produce
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 70

70 EPL Commands
b
b
2D Bar Code – MaxiCode Specific Options
Description Use this command to generate MaxiCode bar code symbols with a single
command. The printer will automatically interpret and encode data into MaxiCode symbols
for data modes 2, 3, 4, and 6. Up to eight symbols can be linked.
Syntax bp ,p ,p ,[p ,][p ,]“DATA”
1 2 3 4 5
Parameters This table identifies the parameters for this format:
Parameters Details
p = Horizontal start Horizontal start position (X) in dots.
1
position
p = Vertical start position Vertical start position (Y) in dots.
2
p = M - Selects —
3
MaxiCode bar code
p (c)= Mode selection
4
Value Description
Not Used Automatic selection Mode 2 or 3
m2 Mode 2
m3 Mode 3
m4 Mode 4
m6 Mode 6
1. If p4 (mx) is not used, the printer will use the following
rules to automatically format the “DATA” parameter. If
the postal code (third parameter, PC) in the “DATA” is:
• All numeric characters, the printer will automatically
select Mode 2.
• Alpha only or alpha-numeric character combinations
will set the printer to Mode 3.
• Not used, the printer automatically selects Mode 3.
2. If p4 value is “m2orm3”, the printerwill use the following
rules to format the “Data” parameter:
• In Mode 2 - If a non-numeric character is entered in the
Postal Code “Data” parameter field, then the
MaxiCode bar code will not print.
• In Mode 3 – If the Postal Code “Data” field exceed 6
characters, then the additional characters will be
truncated from the bar code field.
14245L-001 Rev. A EPL Programming Guide 5/30/07

---

## Pagina 71

EPL Commands 71
b
Parameters Details
p = x,y
5
Associated MaxiCode symbol numbering where:
x = Symbol Number of
y = Total Number of Associated Symbols
Accepted Values: 1-8 for both x or y
Default Value: Not used
"DATA"=Mode dependent Mode dependent data is bounded by quotation marks.
data format Maximum of 2 KBytes of data.
MMooddee DDaattaa FFoorrmmaatt
22 && 33 ““ccll,,ccoo,,ppcc,,llppmm””
44 && 66 ““llppmm””
cl = Class Code (3 digits required)
co = Country Code (3 digits required)
Mode 2 = Numeric Characters
Mode 3 = International Characters (up to 6 characters)
pc = Postal Code
Mode 2 = 5 or 9 characters
(All Numeric, including USA Postal ZIP 5 or 9 char.) For
less than 9 characters, the printer will pad the field with
0’s.
Mode 3 (International)= Any alphanumeric character (up
to 6 characters)
lpm = Low priority message (data)
ASCII printable characters (up to 84 characters per
symbol), any 256 character map.
The programmer should rely on the symbology’s specification to insure format compliance
and proper implementation. See the AIM web site for specifications at:
http://www.aimglobal.org/
5/30/07 EPL Programming Guide 14245L-001 Rev. A

---

## Pagina 72

72 EPL Commands
b
Example •
N↵
b20,20,M,"300,840,93065,1692,This is MaxiCode, but not
MaxiCode formatted data"↵
P1↵
will produce
Using AIM Specified MaxiCode Data Formatting
The EPL printer can use and automatically decode the AIM ITS (International Technical
Standards) MaxiCode data format. The printer detects the message/start header ([)>RS), field
separator (GS), and the end of message marker (RS EOT) data control strings.
The hexadecimal (ASCII) data control strings are in the following table. See the EPL2 dump
mode character map in Appendix A.
Control String Hexadecimal Code
Message/Start Header
[ ) > RS 5B 29 3E 1E
Field Separator
GS 1D
End Of Message Marker
RS EOT 1E 04
Syntax bp1,p2,M,p4 “[AIM MaxiCode Data]”
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 73

EPL Commands 73
b
Example •
b20,400,M,m2”001,840,93065,1692,[)>R
S
01G S98XXXZZFDAAFG SSHIPGS309G
S
G S1/1G S10G
S
NG
S
G SCAMARILLOG SCAG
S
R
S
E OT!!!!!!!!!!!!!!!!!!!!!!!”↵
Note •
1. This programming example represents actual data used to format a single AIM-
compliant MaxiCode symbol as programmed by a major international and domestic
shipping company.
2. The shipper has explicitly set the MaxiCode symbol for Mode 2. This can be omitted by
the programmer and the printer will auto-select the mode per the rules on page70.
3. The shipper has used the “!” character to pad the symbol’s data. A scanner reads back all
the “Data” within the quotation marks, including the “!” characters following the End
of Message Marker (E ).
OT
4. All of the data fields in the Low Priority Message are not used in the example. Some are
left empty with the field delimiting G character used as a format field holder.
S
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 74

74 EPL Commands
b
b
2D Bar Code – PDF417 Specific Options
Description Use this command to print PDF 417 and Macro PDF bar code symbols. The
printer will automatically change from PDF417 to Macro PDF bar code mode if the data sent
to the printer exceeds the maximum amount supported by the PDF417 symbol. The oH
command is used to place the addition Macro PDF symbols needed for the continuation data.
The printer will automatically optimize the symbol for readability of data (and use the
minimum number of symbols when using Macro PDF). The symbol’s geometry is adjusted
(typically reducing the size of the symbol) per the defined parameters. The printer will use the
largest module size (bar width and height) and minimize the number of rows and columns.
Syntax bp ,p ,p ,p ,p [,p ][,p ][,p ][,p ][,p ][,p ][,p ][,p ]
1 2 3 4 5 6 7 8 9 10 11 12 13
[,p ][,p ],”DATA”
14 15
Parameters This table identifies the parameters for this format:
Parameters Details
p = Horizontal start Horizontal start position (X) in dots.
1
position
p = Vertical start position Vertical start position (Y) in dots.
2
p = P Must be “P” for PDF 417 bar codes.
3
p (www) = Maximum Maximum print width in dots.
4
print width
p (hhh) = Maximum Maximum print height in dots
5
print height
The programmer should rely on the symbology’s specification to insure format compliance
and proper implementation. See the AIM web site for specifications at:
http://www.aimglobal.org/
Note • The following parameters may be omitted and default values will
automatically be inserted. Each parameter value (data string) must be proceeded by
its associated command prefix character.
14245L-001 Rev. A EPL Programming Guide 5/30/07

---

## Pagina 75

EPL Commands 75
b
Parameters Details
p (s) = Sets error Error Correction codewords per symbol. If level is not
6
correction level specified, a level will automatically be assigned as per the
following table:
Accepted Values: s1 - s8
EC Level EC Codewords Auto Select Level
0 2 —
1 4 0–31
2 8 32–63
3 16 64–127
4 32 128–255
5 64 256–511
6 128 512–928
7 256 —
8 512 —
5/30/07 EPL Programming Guide 14245L-001 Rev. A

| EC Codewords |
| --- |
| 2 |
| 4 |
| 8 |
| 16 |
| 32 |
| 64 |
| 128 |
| 256 |
| 512 |


---

## Pagina 76

76 EPL Commands
b
Parameters Details
p = selects data Accepted Values: 0 or 1
7
compaction (compression) Default Value: 0
method c0 = Auto-encoding
The printer will switch between the three compaction
modes as needed to create the smallest possible symbol for
the given data.
c1 = Binary mode
The printer will encode the symbol in byte compaction
mode.
PDF417 uses an intermediate data type called a “codeword”
to store the characters in the symbol. Each codeword typically
consumes a fixed-size portion of the total symbol. More
characters inserted into a codeword results in fewer
codewords needed to create a symbol. This results in a
smaller symbol. To provide efficient data compaction, PDF-
417 supports three types of codewords: text, numeric and
binary.
Auto-Encoding (c0) data compaction method is set by
default, and provides the best compaction. If Binary data
compaction (c1) is selected the symbol will typically be
larger. Binary data compaction may help to minimize the
amount of time it takes a system to scan and decode the data
encoded within the symbol.
Data Type Compaction (Byte by Byte)
Text 2 Characters per codeword
Numeric 2.93 Characters per codeword
Binary 1.2 Bytes per codeword
• Text compaction can be used for uppercase, lowercase,
numbers, space, carriage return, tab, line feed, and the
following characters:
&,:#-.$/+%*=^;@[\]_’~!"|(){}`
With text compaction, up to two characters can be
encoded in a single codeword.
• Numeric compaction can be used for numbers only. Up to
2.93 characters can be encoded in a single codeword.
• Byte compaction can be used for any character. But the
flexibility comes at a price; byte compaction encodes only
1.2 characters per codeword.
p (pxxx,yyy,mm)= This parameter is a non-standard implementor of the PDF417
8
print human readable and is only recommended for troubleshooting purposes.
Additional variables:
p = "p" - parameter identifier
xxx = horizontal start location
yyy = vertical start location
mm = maximum characters per line
14245L-001 Rev. A EPL Programming Guide 5/30/07

---

## Pagina 77

EPL Commands 77
b
Parameters Details
p (f)= Bar code origin Accepted Values: 0 or 1
9
point Default Value: 1
f1 - Center of bar code as defined by the automatically
adjusted symbol size, i.e. width and height. Parameters p
4
and p values are maximum values only.
5
f0 - Upper left corner of bar code.
p (x)= module width (in Accepted Values: 2 - 9 (i.e. x2-x9)
10
dots) Default Value: Auto selects 6 (dots).
Tests data with maximum size limit set by p and p and then
4 5
the other optional parameters. The printer automatically
reduces the module width in one dot increments until the data
fits within the symbols maximum dimensions (and other
applied parameters) or until 3 dots has failed, then reports an
error.
p (y) = set bar height Accepted Values: 4 – 99 (i.e. y4-y99)
11
(in dots) Default Value: 4 times module width (p )
10
p (r) = maximum row Maximum limit for the number of rows to be used for auto
12
count selecting symbol features.
p (l) = maximum Maximum limit for the number of columns to be used for auto
13
column count selecting symbol features.
p (t) = truncated flag Accepted Values:
14
0 = not truncated
1 = truncated
See the PDF 417 specification for details.
5/30/07 EPL Programming Guide 14245L-001 Rev. A

---

## Pagina 78

78 EPL Commands
b
Parameters Details
p (o) = rotation Accepted Values:
15
0 = 0º
1 = 90º
2 = 180º
3 = 270º
Settings of 90° & 270° will cause the symbols maximum
height (p ) andwidth (p ) values to transpose when
4 5
automatically calculating and generating the symbol, i.e.
the height would affect column dimensions and width
would affect row dimensions.
“DATA” = ASCII data or Represents a fixed data field.
binary data bytes The backslash (\) character designates the following character
is a literal and will encode into the data field.
To Print Enter into data field
" \"
“Company” \”Company\”
\ \\
\code\ \\code\\
ø \↵
PDF 417: General Information
A PDF417 symbol is organized into minimum of 3 to a maximum of 90 rows and a minimum
of 5 to a maximum of 34 columns of codewords.
Each codeword is 17 modules wide. There are 4 bars and 4 spaces per codeword.
Multiply the module width (in dots, p10) by 17 to get the codeword width.
Multiply the module height (in dots, p11) by the number of rows to get the symbol height.
Four of the codewords in each row are start, stop and two row indicators. The remaining
codewords are referred to as the data region and contain symbol overhead and compacted data.
There can be no more than 928 codewords in the data region. All combinations of rows and
columns are not legal; 90 rows times 30 columns would produce a data region of 2700
codewords which exceeds the 928 codeword maximum per symbol. See the following table
(on the next page) that shows the maximum number of rows and the resulting number of
codewords in the data region for each column count.
14245L-001 Rev. A EPL Programming Guide 5/30/07

---

## Pagina 79

EPL Commands 79
b
PDF 417 Symbol Geometry
Maximum Maximum
Columns Codewords Columns Codewords
Rows Rows
5 90 90 18 66 924
6 90 180 19 61 915
7 90 270 20 58 928
8 90 360 21 54 918
9 90 450 22 51 918
10 90 540 23 48 912
11 90 630 24 46 920
12 90 720 25 44 924
13 90 810 26 42 924
14 90 900 27 40 920
15 84 924 28 38 912
16 77 924 29 37 925
17 71 923 30 35 910
18 66 924 31 34 918
19 61 915 32 33 924
20 58 928 33 32 928
21 54 918 34 30 900
Automatic PDF 417 Bar Code Generation
The printer automatically tests and changes the PDF 417 bar code geometry to maximize the
readability of the bar code for a given maximum height and width, specified by p and p .
4 5
The printer tests the PDF 417 parameters in this order for a given data string (error correction
and compression included):
1. Module width p (for codeword width)
10
2. Symbol width p
4
3. Symbol column maximum p
13
4. Module height p
11
5. Symbol height p
5
6. Symbol row maximum p
12
The printer will start with the maximum value (default or explicit) for these parameters. The
printer reduces these values to get the module width and height to maximize readability.
5/30/07 EPL Programming Guide 14245L-001 Rev. A

| Columns | Maximum<br>Rows | Codewords |
| --- | --- | --- |
| 5 | 90 | 90 |
| 6 | 90 | 180 |
| 7 | 90 | 270 |
| 8 | 90 | 360 |
| 9 | 90 | 450 |
| 10 | 90 | 540 |
| 11 | 90 | 630 |
| 12 | 90 | 720 |
| 13 | 90 | 810 |
| 14 | 90 | 900 |
| 15 | 84 | 924 |
| 16 | 77 | 924 |
| 17 | 71 | 923 |
| 18 | 66 | 924 |
| 19 | 61 | 915 |
| 20 | 58 | 928 |
| 21 | 54 | 918 |


| Columns | Maximum<br>Rows | Codewords |
| --- | --- | --- |
| 18 | 66 | 924 |
| 19 | 61 | 915 |
| 20 | 58 | 928 |
| 21 | 54 | 918 |
| 22 | 51 | 918 |
| 23 | 48 | 912 |
| 24 | 46 | 920 |
| 25 | 44 | 924 |
| 26 | 42 | 924 |
| 27 | 40 | 920 |
| 28 | 38 | 912 |
| 29 | 37 | 925 |
| 30 | 35 | 910 |
| 31 | 34 | 918 |
| 32 | 33 | 924 |
| 33 | 32 | 928 |
| 34 | 30 | 900 |


---

## Pagina 80

80 EPL Commands
b
Example •
N↵
b80,100,P,700,600,x2,y7,l100,r100,f0,s5," \ ↵
Fourscore and seven years ago our fathers brought forth
on this continent a new nation, conceived in liberty and
dedicated to the proposition that all men are created
equal. Now we are engaged in a great civil war, testing
whether that nation or any nation so conceived and so
dedicated can long endure.↵
“↵
ø
b80,200,P,400,300,p40,440,20,f1,x3,y10,r60,l5,"ABCDEFGHI
JK1234567890abcdefghijk"↵
P↵
will produce this label:
The second symbol has been set to print human readable data with the p
8
parameter
(p40,440,20) and is not part of the PDF417 symbol.
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 81

EPL Commands 81
b
b
2D Bar Code – QR Code Specific Options
Description Japanese printer models only. Use this command to generate QR Code bar
code symbols with a single command. See the AIM web site for QR Code specifications at
http://www.aimglobal.org/
Syntax bp ,p ,p ,[p ]”DATA”
1 2 3 4-9
Parameters This table identifies the parameters for this format:
Parameters Details
p = Horizontal start Horizontal start position (X) in dots.
1
position
p = Vertical start position Vertical start position (Y) in dots.
2
p = Q Must be “Q” for QR Code.
3
Parameters p through p are optional and may be omitted. Default values will
4 8
automatically be inserted when a parameter is omitted. Each parameter value must be
proceeded by its associated command prefix character. The parameters p through p can
4 8
be inserted into the command string in any order prior to the “DATA” and following p .
3
Commas between parameters p through p are not required.
4 8
p = Code Model Accepted Values:
4
(prefix m) 1 = Model 1
2 = Model 2
Default Value: Model 2
p = Scale Factor Accepted Values: 1–99
5
(prefix s) Default Value: 3
p = Error Correction Accepted Values:
6
Level L = Lower error correction, most data
(prefix e)
M = Default
Q = Optimized for error correction over data
H = Highest error correction, least data
Default Value: M
p = Data Input Mode Accepted Values:
7
(prefix i) A = Automatic Data Select
M = Initialized the manual data mode and the data type is
set by the first character in the fixed data field (“DATA”).
Default Value: A
5/30/07 EPL Programming Guide 14245L-001 Rev. A

---

## Pagina 82

82 EPL Commands
b
Parameters Details
p = Append Symbol The Append Symbol parameter option allows the programmer
8
(prefix D) to join data from 2 to 16 QR code symbols.
Sub-prefix Values Description
c 01-16 Symbol Number
d 01-16 Divisions
p 00-FF Hex. Parity
“DATA” = Represents a Data sent to the printer is converted to one of four formats
fixed data field depending upon the value set by parameter p , Data Input
7
Mode select. By default, the printer will automatically select
the data mode for the entire fixed data string. The printer will
check and change the data encoding method to achieve the
highest data compression.
If parameter p is set to IM, then first character in the Data
7
must be one of the following:
N - Numeric (0-9)
A - Alphanumeric (0-9, A-Z, a-z and space,$,%, *,+, -, . , /, : )
K - Kanji (Shift JIS character ranges 8140-9FFC and E040-
EAA4 Hex)
B - Binary
The data field has reserved characters that normally can not
be used within the data string, they are: “ and / .
The backslash (\) character designates the following character
is a literal and will encode into the data field. Refer to the
following examples:
To Print Enter into data field
" \"
“Company” \”Company\”
\ \\
\code\ \\code\\
ø \↵
14245L-001 Rev. A EPL Programming Guide 5/30/07

| Values |
| --- |
| 01-16 |
| 01-16 |
| 00-FF Hex. |


---

## Pagina 83

EPL Commands 83
C
C
Counter
Description The counter (C) command defines one of 10 automatic counters used in
consecutive numbering applications (i.e. serial numbers). Counters must be defined after
variables.
For Numeric Serialization Only. The counter function does not support Alpha or Alpha-
Numeric Serialization.
The C command is used in forms that require sequential numbering. When initializing
counters, they must be defined in order (e.g. C0 first, C1 second...).
Field justification (p3) affects the printing of counter data. When L, R or C are selected, the
counter field is the width of p2 value. Data will justify within the counter (p2) field per the
selected p3. The N parameter will print the minimum number of characters.
To print the contents of the counter, the counter number is referenced in the “DATA” field of
the A (ASCII text) or B (Bar Code) commands.
Syntax Cp ,p ,p ,p ”[-]PROMPT”
1 2 3 4
Parameters This table identifies the parameters for this format:
Parameters Details
p = Counter number Accepted Values: 0–9
1
p = Maximum number of Accepted Values: 1–29
2
digits for counter
p = Field Justification Accepted Values:
3
L = Left
R = Right
C = Center
N = No Justification
p = Step Value Accepted Values: + or - sign followed by a single digit of 1–9.
4
Using a step value of +0 allows the counter to be used as an
additional variable data field.
”PROMPT” An ASCII text field that will be transmitted to the KDU or
host (via the serial interface) each time the command is
executed. Typically used to request the operator to enter a
starting counter value.
[-]=KDU Prompt Having the first character of the prompt a single minus sign
Options will cause the prompt to display only once after form
retrieval.
The C command is used in forms that require sequential numbering. When initializing
counters, they must be defined in order (e.g. C0 first, C1 second...).
5/30/07 EPL Programming Guide 14245L-001 Rev. A

---

## Pagina 84

84 EPL Commands
C
Field justification (p ) affects the printing of counter data. When L, R or C are selected, the
3
counter field is the width of p
2
value. Data will justify within the counter (p
2
) field per the
selected p . The N parameter will print the minimum number of characters.
3
To print the contents of the counter, the counter number is referenced in the “DATA” field of
the A (ASCII text) or B (Bar Code) commands.
Note • If the starting value of a counter is “1“, then no leading zero padding will be added. If
the starting value is “01”, then the counter will be padded, up to the maximum number of
digits (p ), with zeros.
2
Example 1 •
C0,10,L,+1,"-Enter Serial Number:"↵
Example 2 • Saving and Protecting Consecutive Numbers in Nonvolatile Memory
This feature is useful when the counter field represents a serial number (or others types of
numbers) that should never be repeated. This feature allows for automatic retrieval and
increment (or decrement) of the previous counter value used every time a form is retrieved
(and printed).
By placing one minus sign as the first character of the prompt, the prompt will appear only
once after the form is retrieved, thereby protecting the integrity of the data.
Example 3 • Single Digit Summation with Counters
Add or subtract a single digit from the recalled counter value in a form. If form recalled
counter C0 had a value of 3, then processing C0+1 would yield a value of 4 and C0-2
would yield a value of 1.
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


|  |  |
| --- | --- |
|  |  |


|  |  |
| --- | --- |
|  |  |


---

## Pagina 85

EPL Commands 85
C
C
Cut Immediate
Description This command allows the printer to initiate an immediate media cut without a
form print operation. The printer must have the cutter option installed.
• The C command – Cut Immediate can not be used inside of a form.
• The initial character C in a command string is used for both the Cut Immediate (C) and
Counter Command function (Cp1) which can only be used within a form. The Cut
Immediate Command (C) can not be used in a form.
• The C command – Cut Immediate can not be used with the KDU.
Mobile printers, such as the TR 220, ignore this command.
Syntax C
Parameters There are no parameters for this command.
Example •
C↵
Note • Use only cut label liner (backing) or tag stock. Label adhesive will build up on the
cutter blade and cause the cutter to operate poorly or jam if the labels are cut along with the
label liner.
Use the C command - Cut Immediate 5 times without media loaded, to perform a self
cleaning of the cutter blade.
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 86

86 EPL Commands
D
D
Density
Description Use this command to select the print density. The density command controls
the amount of heat produced by the print head. More heat will produce a darker image. Too
much heat can cause the printed image to distort.
Syntax Dp
1
Parameters This table identifies the parameters for this format:
Parameters Details
p = Density setting Accepted Values: 0–15
1
Default Value:
2443 (Orion) and 2884: 10
All other printers: 7
Note • 0 is the lightest print and 15 is the darkest.
Note • The density and speed commands can dramatically affect print quality. Changes in the
speed setting typically require a change to the print density.
Example • This example selects density 5.
D5↵
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 87

EPL Commands 87
dump
dump
Enable Dump Mode
Description This command allows the advanced programmer to force a user diagnostic
“data dump” mode. Sending the dump command to the printer allows the programmer to
compare actual data sent to printer with the host program.
Send data to the printer after the dump command has been issued to evaluate program and
printer control data. The printer will process all data bytes into ASCII character data, range 0-
255 decimal (00-FF hexadecimal).
Press the printer’s Feed button until “Out of Dump” is printed or power cycle the printer to
terminate the dump mode.
Syntax dump
Parameters There are no parameters for this format.
• Set the image buffer width with the q command to match the media width prior to issuing
the dump command.
• Use the Dump Mode Character Map onpage319 to interpret the dump mode data
(characters printed on the labels) back into ASCII data.
• Press the Feed button to view dump data that exceeds a single label¡¯s print area. Repeat
to view more dump data as required.
• Pressing the Feed button after the dump data is finished printing will cause the printer to
exit the dump mode.
• Graphics data dump may be large and require multiple labels to print.
Example •
dump↵
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 88

88 EPL Commands
EI
EI
Print Soft Font Information
Description This command will cause the printer to print a list of all soft fonts that are
stored in memory.
Note • Soft fonts can be downloaded to and deleted from the printer using the Soft Font
Downloader Utility or Zebra Designer.
Syntax EI
Parameters There are no parameters for this format.
Example • This example prints a soft font list.
EI↵
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 89

EPL Commands 89
EK
EK
Delete Soft Font
Description This command is used to delete soft fonts from memory.
Note • Soft fonts can be downloaded to and deleted from the printer using the Soft Font
Downloader Utility or Zebra Designer.
Syntax EK {”FONTNAME”|”*”}
Parameters This table identifies the parameters for this format:
Parameters Details
FONTNAME By entering the name of a font, that font will be deleted from
memory.
* By including an “*” (wild card), ALL fonts will be deleted
from memory.
Example 1 • This example deletes font “A”
EK”A”↵
Example 2 • This example deletes all fonts.
EK”*”↵
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


|  |  |
| --- | --- |
|  |  |


---

## Pagina 90

90 EPL Commands
eR
eR
User Defined Error/Status Character
Description This command allows the advanced programmer to specify the printer’s
error/status report character for error reporting via the RS-232 serial interface.
Mobile printers, such as the TR 220, ignore this command.
Syntax eRp ,p
1 2
Parameters This table identifies the parameters for this format:
Parameters Details
p = Any single ASCII Accepted Values: 0–255 decimal (00–FF hexadecimal)
1
character
p = Error/Status
2
Response Mode p Mode Descriptions
2
0 Standard (default):
XON (17 dec. / 11 hex.) on Recovery
XOFF (19 dec. / 13 hex.) on Error
1 Character Only:
Reports the selected error/status character followed
by a Carriage Return and Line Feed.
2 Character & Error/Status Code:
Reports the selected error/status character,
error/status code (see the ^ee command onpage173
for codes), and then by a Carriage Return and Line
Feed.
Example • For Mode 2 Error and Status Reporting:
eR$,2↵ : Sets Error Character to “$”
and
: Sets Error Mode to “2".
: User operates and prints with
printer.
: User opens print head.
$11↵ : Reports Print Head Open
: User closes print head
$00↵ : Reports No Error
: Printer Ready for next
command.
: (Status report for Print Head
Closed)
14245L-001 Rev. A EPL Programming Guide 5/30/07

| p<br>2 |
| --- |
| 0 |
| 1 |


|  |  |
| --- | --- |
|  |  |


---

## Pagina 91

EPL Commands 91
ES
ES
Store Soft Font
Description This command is used to download and store soft fonts in memory.
Note • Soft fonts can be downloaded to and deleted from the printer using the Soft Font
Downloader Utility or Zebra Designer.
Syntax ES”FONTNAME”p p p a b c ”DATA”a b c ”DATA ”a b c ”DATA ”
1 2 3 1 1 1 2 2 2 2 n n n n
Parameters This table identifies the parameters for this format:
Parameters Details
FONTNAME = One letter Accepted Values: a–z, lower case
font name Lower Case named fonts minimize soft font memory usage to
only store fonts downloaded and have 256 character limit.
The following use hexadecimal coding for parameter values.
p = Number of characters Accepted Values: 00–FF hex (0–255 decimal) for 1 to 256
1
to be downloaded fonts per soft font set.
p = Character rotation Accepted Values:
2
00 hex = 0 and 180 degrees
01 hex = 90 and 270 degrees
02 hex = Both 0 and 180 degree rotation pair and the
90 and 270 degree rotation pair
p = Font height Accepted Values: 00–FF hex
3
Measured in dots and expressed as a hexadecimal number, i.e.
1B hex. = 27 dots.
Font height includes accentors and dissenters of characters
and need to fit in the character cell.
• 203 dpi printers = 256 dots = 1.26 in. = 32.03 mm
• 300 dpi printers = 00–FF hex.
256 dots = 0.85 in. = 21.67 mm
a = (1st) Download Accepted Values: 00–FF hex
1
character map
position
b = (1st) Spacing to next Accepted Values: 00–FF hex
1
print character Downloaded character’s next printed character position in
dots, i.e. Character tracking - the space between characters.
Must be greater than or equal to the character width, see
parameter c1. Dots in a decimal number converted to a
hexadecimal number.
c = (1st) Downloaded Accepted Values: 00–FF hex
1
characters width Dots in a decimal number converted to a hexadecimal
number.
5/30/07 EPL Programming Guide 14245L-001 Rev. A

---

## Pagina 92

92 EPL Commands
ES
Parameters Details
“DATA” = (1st) Character p × c = bit map data (in bytes)
3 1
bitmap Data is received in bytes, on a line by line basis. The font
character’s 0,0 cell map position is in the top left corner of the
map as viewed in the 0 degree rotation. See the examples on
the following pages.
a = (2nd) Download Accepted Values: 00–FF hex
2
character map
position
b = (2nd) Spacing to next Accepted Values: 00–FF hex
2
print character
c = (2nd) Downloaded Accepted Values: 00–FF hex
2
characters width
“DATA ” = (2nd) p × c bytes = bit map data
2 3 2
Character bitmap
Repeat for each character until the last character in the set is downloaded.
a = (Last) Download Accepted Values: 00–FF hex
n
character map
position
b = (Last) Spacing to Accepted Values: 00–FF hex
n
next print character
c = (Last) Downloaded Accepted Values: 00–FF hex
n
characters width
“DATA ” =Character p × c bytes = bit map data
n 3 n
bitmap
For fonts with the rotation parameter set for “both” (p2 = 02 hex.):
Repeat the individual font character download for each 90° rotated character from the start of
the character set until the last rotated character in the set is downloaded.
a1-90° b1-90° c1-90° “DATA1-90°”
a2-90° b2-90° c2-90° “DATA2-90°”
a3-90° b3-90° c3-90° “DATA3-90°”
an-90° : (Last) Download Character
bn-90° : (Last) Spacing To Next Print Character
cn-90° : (Last) Downloaded Character’s Width
“DATAn-90°” : Character Bitmap
p3 × cn bytes = bit map data
The number of individual character maps downloaded will be double the characters in
the font set (p ).
1
14245L-001 Rev. A EPL Programming Guide 5/30/07

---

## Pagina 93

EPL Commands 93
ES
Example 1 • This is an example of measuring a soft font size.
(cid:53)(cid:72)(cid:73)(cid:72)(cid:85)(cid:72)(cid:81)(cid:70)(cid:72)(cid:3)(cid:51)(cid:82)(cid:76)(cid:81)(cid:87)
(cid:20)(cid:19)(cid:3)(cid:39)(cid:82)(cid:87)(cid:86)
(cid:20)(cid:21)
(cid:39)
(cid:82)
(cid:87)
(cid:86)
(cid:44)(cid:81)(cid:87)(cid:72)(cid:85)(cid:16)(cid:70)(cid:75)(cid:68)(cid:85)(cid:68)(cid:70)(cid:87)(cid:72)(cid:85)(cid:3)(cid:54)(cid:83)(cid:68)(cid:70)
(cid:27)(cid:3)(cid:39)(cid:82)(cid:87)(cid:86) (cid:11)(cid:68)(cid:70)(cid:87)(cid:88)(cid:68)(cid:79)(cid:79)(cid:92)(cid:3)(cid:90)(cid:75)(cid:76)(cid:87)(cid:72)(cid:3)(cid:71)(cid:82)(cid:87)(cid:86)(cid:12)
Parameter Dots Data Enteres as Hexadecimal
p 12 0C hex.
3
b 10 0A hex.
c 8 08 hex.
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |


| Dots |
| --- |
| 12 |
| 10 |
| 8 |


---

## Pagina 94

94 EPL Commands
ES
Example 2 • This is an example of soft fonts programming code.
The typical soft font downloads command strings to the printer. The following example was
generated with the CAL3 software.
CR &LF CR &LF p1 p2
p3 a1 b1 c1
a2 b2 c2
a3 b3 c3
CR &LF
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 95

EPL Commands 95
ES
Example 3 • This is an example of font bitmap data format.
The black and white bitmap that represents the font must be converted into ASCII
hexadecimal code. The 0° font format has dot converted to data bytes reading from left to
right and the last byte in a line is padded with zeros to complete the line and data byte.
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 96

96 EPL Commands
f
f
Cut Position
Description Use this command on an individual printer to provide precision cut placement
to:
• Compensate for small sensor to cutter position differences on a printer by printer basis.
• Fine-tune the cut position to compensate for differences in media.
Mobile printers, such as the TR 220, ignore this command.
When using the label liner cutter option, the printerwill advance each printed label to the
appropriate programmed offset cut position, between labels, before cutting. Due to media
differences, the printer may not accurately position the labels before cutting, causing the cutter
to cut the label instead of the liner.
Note • The printer’s cutter is not designed to cut labels. Labels have adhesive that may
interfere with the proper operation of the cutter.
Only cut label liner and tag stock and do not exceed the specified media density and
thickness of the cutter.
If the cut position causes the label just printed to be cut, increase the cut position index value
(>100). If the cut position causes the label following the one just printed to be cut, decrease the
cut position index value (<100).
Syntax fp
1
Parameters This table identifies the parameters for this format:
Parameters Details
p = Cut position index Cut position index measured in dots.
1
Accepted Values: 070 to 130
Accepted Values: 100.
14245L-001 Rev. A EPL Programming Guide 5/30/07

---

## Pagina 97

EPL Commands 97
fB
fB
Adjust Backup Position
Description Use this command to provide precision tear, peel and cut placement to fine-
tune the media positioning to compensate for differences in media and handling requirements.
Mobile printers, such as the TR 220, ignore this command.
Syntax fp
1
Parameters This table identifies the parameters for this format:
Parameters Details
p = Media position offset Media position offset measured in dots.
1
Accepted Values: 0–255
Default Value: 0
5/30/07 EPL Programming Guide 14245L-001 Rev. A

---

## Pagina 98

98 EPL Commands
FE
FE
End Form Store
Description This command is used to end a form store sequence.
Syntax FE
Parameters There are no parameters for this format.
Example • The form store sequence is started with the FS command.
FS"FORMNAME"↵
...
FE↵
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 99

EPL Commands 99
FI
FI
Print Form Information
Description This command will cause the printer to print a list of all forms stored in
memory.
Syntax FI
Parameters There are no parameters for this format.
Example • This command will print a forms list.
FI↵
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 100

100 EPL Commands
FK
FK
Delete Form
Description this command is used to delete forms from memory.
Syntax FK [ “FORMNAME” | “*”]
Parameters This table identifies the parameters for this format:
Parameters Details
“FORMNAME” By entering the name of a form, that form will be deleted
from memory.
• The namemay be up to 8 characters long.
• Form names stored by the printer are case sensitive and
will be stored exactly as entered on the FS command line;
i.e. “FORM1”, “form1” and “FoRm1” are three different
forms when stored into the printer or when retrieved by
the user.
• Deleting a single form requires the FK”FORMNAME” be
issued twice for each form to be deleted. Some label
generation programs re-issue forms (form delete and
store) every time a label is printed which reduces flash
memory life.
“*” = Wild card By including an “*” (wild card), ALL forms will be deleted
from memory.
The FK”*” does not need to be issued twice to delete all
forms.
Example 1 • This example deletes the AFORM form. A second delete is requierd for flash
printers.
FK"AFORM"↵
FK"AFORM"↵
Example 2 • This example deletes all forms.
FK"*"↵
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


|  |  |
| --- | --- |
|  |  |


---

## Pagina 101

EPL Commands 101
FR
FR
Retrieve Form
Description Use this command to retrieve a form that was previoiusly stored in memory.
Syntax FR”FORMNAME”
Parameters This table identifies the parameters for this format:
Parameters Details
“FORMNAME” This is the form name used when the form was stored.
• The namemay be up to 8 characters long.
• Form names stored by the printer are case sensitive and
will be stored exactly as entered on the FS command line;
i.e. “FORM1”, “form1” and “FoRm1” are three different
forms when stored into the printer or when retrieved by
the user.
Example • This example retrieves the form named TEST1.
FR"TEST1"↵
Note • To print a list of the forms currently stored in memory, use the F1 command.
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 102

102 EPL Commands
FS
FS
Store Form
Description This command begins a form store sequence.
• All commands following FS will be stored in form memory until the FE command is
received, ending the form store process.
• Delete a form prior to updating the form by using the FK command. If a form (with the
same name) is already stored in memory, issuing the FS command will result in an error
and the previously stored form is retained.
• To print a list of the forms currently stored in memory, use the FI command.
• Data stored within a form can not have the Null (0 dec. 00 hex.) character as part of any
data within that form.
• A form will not store if insufficient memory is available. See the M command for details
on adjusting and configuring memory for forms, graphics and soft fonts.
Syntax FS”FORMNAME”
Parameters This table identifies the parameters for this format:
Parameters Details
“FORMNAME” This is the form name used when the form was stored.
• The namemay be up to 8 characters long.
• Form names stored by the printer are case sensitive and
will be stored exactly as entered on the FS command line;
i.e. “FORM1”, “form1” and “FoRm1” are three different
forms when stored into the printer or when retrieved by
the user.
• Global commands such as EI, EK, ES, FI, FK, GI, GK,
GM, M, N, P, TS, U, UE, UF, UG, Y, W, ?, ^@ should not be
used in a form store sequence.
Note • Form name, AUTOFR, is reserved for
automatic, single form recall. See AUTOFR
onpage48 for details.
Example •
FK"TESTFORM"↵ : delete form “TESTFORM”
FS"TESTFORM"↵ : begins the form store
sequence of the form
“TESTFORM”
V00,15,N,"Enter Product Name:"↵
B10,20,0,3,2,10,100,B,"998152.001"↵
A50,200,0,3,1,1,N,"Example Form"↵
A50,400,0,3,1,1,N,"Model Name: “V00↵
FE ↵ : ends form store sequence
FI ↵ : prints list of stored forms
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 103

EPL Commands 103
GG
GG
Print Graphics
Description Use this command to print a PCX (format) graphic that has been previously
stored in printer memory.
Syntax GGp ,p ,{N”NAME” | Variable Data}
1 2
Parameters This table identifies the parameters for this format:
Parameters Details
p = Horizontal start Horizontal start position (X) in dots.
1
position
p = Vertical start position Vertical start position (Y) in dots.
2
“NAME” or Variable This is the graphic name used when the graphic was stored.
Data =Graphic This name can be supplied via variable data (V00 - V99).
name • The name may be up to 8 characters long.
• Graphic names stored by the printer are case sensitive and
will be stored exactly as entered with the GM command
line; i.e. “GRAPHIC1”, “graphic1” and “graPHic1” are
three different graphics when stored into the printer or
when retrieved by the user.
Example • This example
FK"TESTFORM"↵ : delete form “TESTFORM”
FS"TESTFORM"↵ : begins the form store
sequence of the form
“TESTFORM”
V00,8,N,"Enter Graphic Name:"↵
GG50,50,V00↵
FE↵ : ends form store sequence
FR"TESTFORM"↵ : retrieves the form named
TESTFORM
?↵ : Download variables
LOGO1↵ : Graphic name to be recalled
and printed
P1↵ : Print one label with graphic
LOGO1
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 104

104 EPL Commands
GI
GI
Print Graphics Information
Description This command will cause the printer to print a list of all graphics stored in
memory.
Syntax GI
Parameters There are no parameters for this command.
Example • This example will print a graphics list.
GI↵
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 105

EPL Commands 105
GK
GK
Delete Graphics
Description Use this command to delete graphics from memory.
Syntax GK {“NAME”|”*”}
Parameters This table identifies the parameters for this format:
Parameters Details
“NAME” By entering the name of a graphic, that graphic will be deleted
from memory.
• Graphic names stored by the printer are case sensitive and
will be stored exactly as entered with the GM command
line; i.e. “LOGO1”, “logo1” and “LoGo1” are three
different graphics when stored into the printer or when
retrieved by the user.
• Deleting a single graphic requires that the
GK”FORMNAME” command string be issued twice for
each form deleted. Some label generation programs re-
issue graphics (graphic delete and store) every time a label
is printed which will reduce flash memory life.
“*” = Wild card By including an “*” (wild card), ALL graphics will be deleted
from memory. The GK”*” does not need to be issued twice to
delete all graphics.
Example 1 • This example deletes the graphic logo. A second delete is requierd for flash
printers.
GK"LOGO"↵
GK"LOGO"↵
Example 2 • This example deletes all graphics.
GK"*"↵
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


|  |  |
| --- | --- |
|  |  |


---

## Pagina 106

106 EPL Commands
GM
GM
Store Graphics
Description Use this command to store PCX graphics files in memory.
Syntax GM”NAME”p
1 <carriage return>
“DATA”
Parameters This table identifies the parameters for this format:
Parameters Details
“NAME” = Graphic name This is the grahpic name that will be used when retrieving the
stored graphic.
• The name may be up to 8 characters long.
• Graphic names stored by the printer are case sensitive and
will be stored exactly as entered with the GM command
line; i.e. “LOGO1”, “logo1” and “LoGo1” are three
different graphics when stored into the printer or when
retrieved by the user.
• Deleting a single graphic requires that the
GK”FORMNAME” command string be issued twice for
each form deleted. Some label generation programs re-
issue graphics (graphic delete and store) every time a label
is printed which will reduce flash memory life.
p = File size in bytes Use the DOS DIR command to determine the exact file size.
1
“DATA” =Graphic data Graphic data in 1-bit (black & white) PCX (binary data)
format file.
• A graphic will not store if sufficient memory is not
allocated to graphic memory. See the M command for
details on adjusting and configuring memory to store
graphics (forms and soft fonts).
• Verify the proper storage of the graphic with the GI
command.
14245L-001 Rev. A EPL Programming Guide 5/30/07

---

## Pagina 107

EPL Commands 107
GM
Example 1 •
GK"LOGO1"↵ : deletes graphic “LOGO1” -
Required
GK"LOGO1"↵ : second delete graphic -
Required
GM"LOGO1"584↵ : Prepares printer to receive
graphic “LOGO1”
DATA↵ : Data string in PCX format
If using a DOS system, the PCX format file (binary data) portion can be sent to the printer
using the DOS COPY command. For example, if you have a PCX file named LOGO1.PCX
in your current directory, the appropriate command would be:
COPY LOGO1.PCX PRN /b
After downloading, the GI command can be used to verify that the graphic was
successfully stored.
Example 2 •
First, create a text file “STOREIT.TXT” with an ASCII text editor, as follows:
GK“WORLD”↵
GK“WORLD”↵
GM"WORLD"2004↵
Where WORLD is the name of the graphic and 2004 is the size (in bytes) of the PCX file.
DO NOT add extra linefeeds to the STOREIT.TXT file.
Next, at the DOS prompt, type:
COPY STOREIT.TXT+WORLD.PCX PRN /b
or use the Zebra Firmware Downloader from Windows OS download the STOREIT.TXT and
WORLD.PCX in sequence to the printer.
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


|  |  |
| --- | --- |
|  |  |


---

## Pagina 108

108 EPL Commands
GW
GW
Direct Graphic Write
Description Use this command to load binary graphic data directly into the Image Buffer
memory for immediate printing. Theprinter does not store graphic data sent directly to the
image buffer.
The graphic data is lost when the image has finished printing, power is removed or the printer
is reset. Commands that size (Q and q) or clear (N and M) the image buffer will also remove
graphic image data.
Syntax GWp ,p ,p ,p ,DATA
1 2 3 4
Parameters This table identifies the parameters for this format:
Parameters Details
p = Horizontal start Horizontal start position (X) in dots.
1
position
p = Vertical start position Vertical start position (Y) in dots.
2
p = Width of graphic Width of graphic in bytes. Eight (8) dots = one (1) byte of
3
data.
p = Length of graphic Length of graphic in dots (or print lines)
4
DATA Raw binary data without graphic file formatting. Data must
be in bytes. Multiply the width in bytes (p ) by the number of
3
print lines (p ) for the total amount of graphic data. The
4
printer automatically calculates the exact size of the data
block based upon this formula.
14245L-001 Rev. A EPL Programming Guide 5/30/07

---

## Pagina 109

EPL Commands 109
i
i
Asian Character Spacing
Description Places an adjustable inter-character space between Asian font characters, fonts
8 and 9, only. The inter-character spacing gets multiplied with the text string by the selected
font’s horizontal and vertical multiplier values.
Syntax ip
1
Parameters This table identifies the parameters for this format:
Parameters Details
p = Space in dots Accepted Values: 0-9 (dots)
1
between Asian Default Value: 0 (dots or no space)
characters
i
( ) Command
Parameter Set to Default (0 dots)
1 Character 1 Character
i
( ) Command
Parameter Set to 8 (8 dots)
1 Character i = 8 1 Character
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |


|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |


---

## Pagina 110

110 EPL Commands
I
I
Character Set Selection
Description Use this command to select the appropriate character set for printing (and
KDU display).
Syntax Ip ,p ,p
1 2 3
Parameters This table identifies the parameters for this format:
Parameters Details
p = Number of data bits Accepted Values:
1
8 = 8 bit data
7 = 7 bit data
14245L-001 Rev. A EPL Programming Guide 5/30/07

---

## Pagina 111

EPL Commands 111
I
Parameters Details
p = Printer
2
Codepage/Language 8 Bit Data 7 Bit Data
Support
p Code Page Description p Description
2 2
0 DOS437 English-US 0 USA
1 DOS850 Latin1 1 British
2 DOS852 Latin2 2 German
(CyrillicII/Slavic)
3 DOS860 Portuguese 3 French
4 DOS863 French 4 Danish
Canadian
5 DOS865 Nordic 5 Italian
6 DOS857 Turkish 6 Spanish
7 DOS861 Icelandic 7 Swedish
8 DOS862 Hebrew 8 Swiss
9 DOS855 Cyrillic
10 DOS866 CyrillicCIS1
11 DOS737 Greek
12 DOS851 Greek1
13 DOS869 Greek2
A Windows1252 Latin1
B Windows1250 Latin2
C Windows1251 Cyrillic
D Windows1253 Greek
E Windows1254 Turkish
F Windows1255 Hebrew
p = KDU Countyr Code 8 bit data only.
3
KDU Country Code (8 bit only)
032 Belgium 049 Germany 027 S.Africa
002 Canada 031 Netherl’ds 034 Spain
045 Denmark 039 Italy 046 Sweden
358 Finland 003 Latin Am. 041 Swizerl’d
033 France 047 Norway 044 U.K.
351 Portugal 001 U.S.A.
The default setting is I8,0,001. See Appendix B, Character References for EPL2 standard font
character samples.
5/30/07 EPL Programming Guide 14245L-001 Rev. A

| 8 Bit Data<br>p Code Page Description<br>2 |  |  |  |
| --- | --- | --- | --- |
| 0 | DOS437 | English-US | 0 |
| 1 | DOS850 | Latin1 | 1 |
| 2 | DOS852 | Latin2<br>(CyrillicII/Slavic) | 2 |
| 3 | DOS860 | Portuguese | 3 |
| 4 | DOS863 | French<br>Canadian | 4 |
| 5 | DOS865 | Nordic | 5 |
| 6 | DOS857 | Turkish | 6 |
| 7 | DOS861 | Icelandic | 7 |
| 8 | DOS862 | Hebrew | 8 |
| 9 | DOS855 | Cyrillic |  |
| 10 | DOS866 | CyrillicCIS1 |  |
| 11 | DOS737 | Greek |  |
| 12 | DOS851 | Greek1 |  |
| 13 | DOS869 | Greek2 |  |
| A | Windows1252 | Latin1 |  |
| B | Windows1250 | Latin2 |  |
| C | Windows1251 | Cyrillic |  |
| D | Windows1253 | Greek |  |
| E | Windows1254 | Turkish |  |
| F | Windows1255 | Hebrew |  |


| 032 | Belgium | 049 | Germany | 027 |
| --- | --- | --- | --- | --- |
| 002 | Canada | 031 | Netherl’ds | 034 |
| 045 | Denmark | 039 | Italy | 046 |
| 358 | Finland | 003 | Latin Am. | 041 |
| 033 | France | 047 | Norway | 044 |
|  |  | 351 | Portugal | 001 |


---

## Pagina 112

112 EPL Commands
JB
JB
Disable Top Of Form Backup
Description This command disables the Top Of Form Backup feature when printing
multiple labels. At power up, Top Of Form Backup will be enabled.
Syntax JB
Parameters Thre are no parameters for this format.
Example •
JB↵
Note • With the JB command enabled, the first label will backup to the Top Of Form before
printing. This preserves the first label which has stopped approximately one-half inch from
the print head. This is the label’s tear away point as set by the previous print operation.
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 113

EPL Commands 113
JC
JC
Disable Top Of Form Backup- All Cases
Description This command disables the Top Of Form Backup feature for all operations.
Use this command for liner-less printing and special media cutting modes.
This command only is available in the 2824, 2844, and 3842 desktop printer models at this
time.
Syntax JC
Parameters Thre are no parameters for this format.
Example •
JC↵
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 114

114 EPL Commands
JF
JF
Enable Top Of Form Backup
Description This command enables the Top Of Form Backup feature and presents the last
label of a batch print operation. Upon request initiating the printing of the next form (or batch),
the last label backs up the Top Of Form before printing the next label.
Syntax JF
Parameters Thre are no parameters for this format.
Example •
JF↵
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 115

EPL Commands 115
LE
LE
Line Draw Exclusive OR
Description Use this command to draw lines with an “Exclusive OR” function. Any area,
line, image or field that this line intersects or overlays will have the image reversed or inverted
(sometimes known as reverse video or a negative image). In other words, all black will be
reversed to white and all white will be reversed to black within the line’s area (width and
length).
Syntax LEp ,p ,p ,p
1 2 3 4
Parameters This table identifies the parameters for this format:
Parameters Details
p = Horizontal start Horizontal start position (X) in dots.
1
position
p = Vertical start position Vertical start position (Y) in dots.
2
p = Horizontal length Horizontal length in dots.
3
p = Vertical length Vertical length in dots.
4
Example •
N↵ : clear image buffer
LE50,200,400,20↵ : draw a line
LE200,50,20,400↵ : draw another line
P1↵ : print one label
will produce this label:
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 116

116 EPL Commands
LO
LO
Line Draw Black
Description Use this command to draw black lines, overwriting previous information.
Syntax LOp ,p ,p ,p
1 2 3 4
Parameters This table identifies the parameters for this format:
Parameters Details
p = Horizontal start Horizontal start position (X) in dots.
1
position
p = Vertical start position Vertical start position (Y) in dots.
2
p = Horizontal length Horizontal length in dots.
3
p = Vertical length Vertical length in dots.
4
Example •
N↵ : clear image buffer
LO50,200,400,20↵ : draw a line
LO200,50,20,400↵ : draw another line
P1↵ : print one label
will produce this label:
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 117

EPL Commands 117
LS
LS
Line Draw Diagonal
Description Use this command to draw diagonal black lines, overwriting previous
information.
Syntax LSp ,p ,p ,p ,p
1 2 3 4 5
Parameters This table identifies the parameters for this format:
Parameters Details
p = Horizontal start Horizontal start position (X) in dots.
1
position
p = Vertical start position Vertical start position (Y) in dots.
2
p = Horizontal length Horizontal length in dots.
3
p = Vertical length Vertical length in dots.
4
p = Vertical end position Vertical end position (Y) in dots.
5
Example •
N↵ : clear image buffer
LS10,10,20,200,200↵ : draw a diagonal line
P1↵ : print one label
will produce this label:
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 118

118 EPL Commands
LW
LW
Line Draw White
Description Use this command to draw white lines, effectively erasing previous
information.
Syntax LWp ,p ,p ,p
1 2 3 4
Parameters This table identifies the parameters for this format:
Parameters Details
p = Horizontal start Horizontal start position (X) in dots.
1
position
p = Vertical start position Vertical start position (Y) in dots.
2
p = Horizontal length Horizontal length in dots.
3
p = Vertical length Vertical length in dots.
4
Example •
N↵ : clear image buffer
LO50,100,400,20↵ : draw black line
LO50,200,400,20↵ : draw another black line
LO50,300,400,20↵ : draw another black line
LW200,50,20,400↵ : draw a white line over all 3
black lines
P1↵ : print 1 label
will produce this label:
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 119

EPL Commands 119
M
M
Memory Allocation
Description Printers except LP 2348 and LP 2348 Plus, with firmware version 4.32 and
above ignore this command. Use this command to set the size of form memory. The reminder
of the form storage memory will be shared by soft fonts and graphics data.
Syntax Mp ,p ,p
1 2 3
Parameters This table identifies the parameters for this format:
Parameters Details
p = Parameter ignored Parameter ignored, but required to process. Represents Image
1
buffer size in whole KBytes.
p = Form(s) memory size Form(s) memory size in whole KBytes. The parameter, p2
2
(form memory size), inversely effects the size of the shared
graphics/soft fonts memory.
p = Parameter ignored Parameter ignored, but required to process. Graphics (and soft
3
font) memory size in whole Kbytes.
If the M command is issued, then all three parameters must be present.
Note • Available memory and the current allocation of memory can be displayed with the U
command or an AutoSense procedure, see the printer’s user manual for details.
5/30/07 EPL Programming Guide 14245L-001 Rev. A

---

## Pagina 120

120 EPL Commands
N
N
Clear Image Buffer
Description This command clears the image buffer prior to building a new label image.
Syntax N
Parameters There are no parameters for this format.
Considerations:
• Do not use the N command within stored forms.
• All printer configuration commands should be issued prior to issuing the N command to
begin building the image for printing within the image buffer.
• Always send a Line Feed (LF) prior to the N command to ensure that previous data in the
command buffer has cleared and the printer is initialized and ready to accept commands.
Example •
↵ : activates command processing
N↵ : clears the image buffer
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 121

EPL Commands 121
o
o
Cancel Software Options
Description This command allows the user to cancel most printer customization parameters
set by o series commands.
Parameters set by the following commands are canceled and returned to default operation:
• oH
• oM
• oE
Syntax o
Parameters There are no parameters for this format.
The o command is a global printer command.
• It can not be issued inside of a form.
• It must be issued prior to issuing a text or bar code command (and printing).
5/30/07 EPL Programming Guide 14245L-001 Rev. A

---

## Pagina 122

122 EPL Commands
oB
oB
Cancel Auto Bar Code Optimization
Description This command allows the advanced programmer to disable bar code
optimization for rotated (90° & 270°) bar codes.
Syntax oB
Parameters Thre are no parameters for this format.
The oB command is a global printer command.
• It can not be issued inside of a form.
• It must be issued prior to issuing a bar code command (and printing).
To reapply bar code defaults, issue a o (small letter ¡°o¡±) command. See page C-90 for
important details on the effects of using the o command.
Reset the printer with a ^@ command with flash firmware printers or cycle printer power to
clear the oB command and return the printer to normal operation.
Important • Zebra Technologies Corporation does not warrant, support, or endorse the use of
bar codes generated by the printer after a oB command has been issued.
Zebra Technologies Corporation does not support this feature other than with the information
supplied in this document.
14245L-001 Rev. A EPL Programming Guide 5/30/07

---

## Pagina 123

EPL Commands 123
oE
oE
Line Mode Font Substitution
Description This command is a Page Mode (EPL2) command that allows the printer to set
alternate Line Mode font character sets. The fonts are activated by the oE command and are
intended for EPL1 emulation.
Mobile printers, such as the TR 220, ignore this command.
Syntax oEp ,p ,p ,p ,p
1 2 3 4 5
Parameters This table identifies the parameters for this format:
Parameters Details
p = 5 x 7 bitmap font 5 x 7 bitmap font - Normal (CCSET4)
1
Line Mode EPL1 Compatibility Font A0
Total character area is 8 x 11 dots
p = 5 x 7 bitmap font 5 x 7 bitmap font - Bold (CCSET4)
2
Line Mode EPL1 Compatibility Font A0
Total character area is 8 x 11 dots
p = 5 x 7 bitmap font 5 x 7 bitmap font - Doubled (CCSET4)
3
Line Mode EPL1 Compatibility Font A0
Total character size is 8 x 11 dots
p = 14 x 22 bitmap font 14 x 22 bitmap font - (CCSET1)
4
Line Mode EPL1 Compatibility Font A
Total character area is 16 x 26 dots
p = 10 x 18 bitmap font 10 x 18 bitmap font - (CCSET3)
5
Line Mode EPL1 Compatibility Font A
Total character area is 12 x 22 dots
• Parameters p1-p5 are preloaded soft fonts.
• Parameters p1-p5 must be all be lower case alpha soft fonts. See the ES command
onpage91 for more details on soft fonts.
• The EPL2 font sets 2 & 4 can be restored as the default Line Mode fonts by sending the o
command without a parameter.
5/30/07 EPL Programming Guide 14245L-001 Rev. A

---

## Pagina 124

124 EPL Commands
oH
oH
Macro PDF Offset
Description Use this command to place addition secondary, associated Macro PDF
symbols for the continuation of data greater than a single PDF 417 bar code can store.
This command must precede any PDF 417 bar code commands in order to print Macro PDF
(multiple bar code) symbols from a single b command’s data field.
Syntax oHp ,p
1 2
Parameters This table identifies the parameters for this format:
Parameters Details
p = Horizontal offset Horizontal offset position (X) in dots of the next Macro PDF
1
position bar code symbol.
p = Vertical offset Vertical offset position (Y) in dots of the next Macro PDF bar
2
position code symbol.
14245L-001 Rev. A EPL Programming Guide 5/30/07

---

## Pagina 125

EPL Commands 125
oH
Example •
N↵
q784↵
Q1215,24↵
R0,0↵
oH0,500↵
ø
N↵
b80,100,P,700,600,x2,y7,l100,r100,f0,s5,"\↵
Fourscore and seven years ago our fathers...↵
<<the rest of Lincoln’s Gettysburg Address HERE>>
... and that government of the people, by the people, for
the people shall not perish from the earth.↵
“↵
P↵
will produce this label:
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


|  |  |
| --- | --- |
|  |  |


---

## Pagina 126

126 EPL Commands
oM
oM
Disable Initial Esc Sequence Feed
Description This command disables the automatic label calibration routine executed by the
printer upon receiving the first escape command sequence from the Windows printer driver.
The printer normally measures a single label and sets the top of form prior to printing the first
label after a power-up reset. The Windows™ printer driver issues escape sequences when
printing.
This command’s primary use is to save preprinted forms such as serialized labels, tags or
tickets.
Mobile printers, such as the TR 220, ignore this command.
Syntax oM
This command must be issued prior to printing with the Windows driver’s or any other
Escape (esc) mode printing operation.
To reinitialize label calibration, issue a o (111 dec. or 6F hex.) command.
Parameters There are no parameters for this format.
14245L-001 Rev. A EPL Programming Guide 5/30/07

---

## Pagina 127

EPL Commands 127
oR
oR
Character Substitution (Euro)
Description This command allows the advanced programmer to substitute the Euro
currency character for any ASCII character in printer resident font numbers 1-4.
The second function this command supports is the zero character style toggling between a
plain zero character and a zero with a slash.
Character substitution settings are stored in the printers non-volatile 'flash' memory. The
original character can be restored by sending the oR command without a parameter.
Syntax oR[p ,p ]
1 2
Parameters This table identifies the parameters for this format:
Parameters Details
p = E If the p parameter is not provided, then the Euro character
1 2
will map to code page position 213 decimal (D5 hexadecimal)
for all code pages.
p = 0 (zero) Toggles the zero character:
1
slash — no slash (out of box default)
p = Decimal number Accepted Values: 0 to 255
2
The active code page’s ASCII character map position to be
replaced by the Euro character. The Euro character will be
active in this map position for all code pages. See the I
command for details on code page selection.
None = No parameters No Parameters (p /p ) resets to all code pages to original
1 2
(p /p ) default character mapping.
1 2
Optionally, to reapply normal character operations, issue a o
(111 dec. or 6F hex.) command. See page121 for important
details on the effects of using this command.
Note • The Euro character is not supported in Font 5 character set.
5/30/07 EPL Programming Guide 14245L-001 Rev. A

---

## Pagina 128

128 EPL Commands
oW
oW
Customize Bar Code Parameters
Description This command allows the advanced programmer to modify specific bar code
parameters to exceed the specified bar code’s design tolerances, i.e. reduce the bar code size.
Note • Using the oW command may cause bar codes to become unreadable by some or all
bar code scanners.
Syntax oWp ,p ,p ,p ,p
1 2 3 4 5
Parameters This table identifies the parameters for this format:
Parameters Details
p = Initial width narrow Default Value: 2
1
white bar
p = Initial width narrow Default Value: 2
2
black bar
p = Initial width wide Default Value: 4
3
white bar
p = Initial width wide Default Value: 4
4
black bar
p = Initial bar code gap Default Value: 3
5
The oW command is a global printer command.
• They can not be issued inside of a form.
• They must be issued prior to issuing a bar code command (and printing).
• Use only one bar code format. Using more than one bar code may cause unpredictable
results or operation.
• Issue all 5 command parameters (p1-5 ). Use the default parameter values as place
holders.
• Bar code printed with this command should be printed in the picket fence orientation (0°
& 180° rotations) to maximize scanning.
Reset the printer with a ^@ or o commands with flash firmware printers or cycle printer power
to clear theoWcommand and return the printer to normal operation. See page C-90 for
important details on the effects of using the o command.
14245L-001 Rev. A EPL Programming Guide 5/30/07

---

## Pagina 129

EPL Commands 129
oW
The B command parameters p & p must beset to 0 to use bar codes customized with this
5 6
command.
Note • The oW command has been tested for parameter functionality for Bar Code 39 only.
The oW command may also function with Codabar and Interleaved 2 of 5 bar code, but they
have not been functionally verified for this command.
Important • Zebra Technologies Corporation does not warrant, support, or endorse the use of
bar codes generated by the printer after aoWcommand has been issued. Zebra Technologies
Corporation does not support this feature other than with the information supplied in this
document.
5/30/07 EPL Programming Guide 14245L-001 Rev. A

---

## Pagina 130

130 EPL Commands
O
O
Hardware Options
Description Use this command to select various printer options. Options available vary by
printer configuration.
Options selected and enabled in a printer can be verified by checking the printer configuration
printout, Dump Mode printer status label. See the U command onpage146 and the
Explanation of the Status Printout onpage34.
Mobile printers, such as the TR 220, ignore this command.
Syntax O[C[p1],D,P,L,S,F]
Parameters This table identifies the parameters for this format:
Parameters Details
C = Enable optional The cutter will cut at the end of each form as specified by the
Label Liner Cutter Q command.
Cp = Batch print labels p p1 = Sets the number of labels to print prior to cut.
1 1
and liner cut If a number between 1 - 255 is specified for p , the printer
1
will cut after the specified number of labels have been
printed.
If b is specified for p , the “batch print & cut” feature is
1
enabled. This feature uses the P command to control cutter
operation.
D = Enable direct Enable Direct Thermal Mode, use this option when using
Thermal Mode direct thermal media in a thermal transfer printer.
d = Status Not a command, this is a status only. Out of box default Direct
Thermal Mode setting used in a 2844, 2824 or 3842 thermal
transfer printer's and is displayed in the Dump Mode status
printout. Changing the printer to thermal transfer mode or
when the printer detects a transfer ribbon will cause this
option parameter to permanently be removed from the status
printout.
P = Enable label taken Enable label taken sensor for the Label Dispense (Peel)
sensor Mode.
L = Enable printer’s Feed Enable the printer's Feed button for Tap to Print operation in
button the Label Dispense (Peel) Mode. The printer will present each
label and wait for a tap of the Feed button before printing the
next label. Use this mode when printing multiple copies of
liner-free labels.
14245L-001 Rev. A EPL Programming Guide 5/30/07

---

## Pagina 131

EPL Commands 131
O
Parameters Details
S = Reverse sensor Reverse the Transmissive (Gap) Sensor’s normal operation.
operation
Fp = Form Feed Setting p = Sets the type of operation the feed button .
1 1
f = Default, normal operation. Tap to feed.
r = Reprint last label printed.
i = Ignore the feed button.
Example •
O↵ : disables all options.
OC↵ : enables cutter only, labels
are cut after each
: label is printed, disables
all other options
OD↵ : enables direct thermal mode
on thermal transfer
: printers, disables all other
options
OCb↵ : labels are cut after a batch
of five has printed,
… : disables all other options
P5↵ : Sets the number of labels to
print before the cut
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 132

132 EPL Commands
OEPL1
OEPL1
Set Line Mode
Description This command is used to switch the printer operating mode from Page Mode
(EPL2) to Line Mode (EPL1 emulation).
Line Mode configuration setting is retained after reset has been issued or power has been
cycled.
Mobile printers, such as the TR 220, ignore this command.
Syntax OEPL1
Parameters There are no parameters for this format.
Example •
OEPL1↵
Returning to Page Mode
The Line Mode command EPL2 can be sent to the printer to return the printer to Page (EPL2)
Mode operation.
The EPL2 command is preceded by an ESCape (27 dec or 1Bh) character and followed by a
line feed (LF - 10 dec or 0A hex), a carriage return (CR - 13 dec or 0D hex) or CR/LF.
Example •
←EPL2↵
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


|  |  |
| --- | --- |
|  |  |


---

## Pagina 133

EPL Commands 133
P
P
Print
Description Use this command to print the contents of the image buffer.
Note • The P command cannot be used inside of a stored form sequence. For automatic
printing of stored forms, use the PA command.
Syntax Pp ,[p ]
1 2
Parameters This table identifies the parameters for this format:
Parameters Details
p = Number of label sets. Accepted Values: 1 to 65535
1
p = Number of copies of Accepted Values: 1 to 65535
2
each labael Number of copies of each label (used in combination with
counters to print multiple copies of the same label).
Example •
P1↵ : prints one label set
P2,1↵ : prints 2 label sets of one
label each
P5,2↵ : prints 5 label sets of 2
labels each
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 134

134 EPL Commands
PA
PA
Print Automatic
Description Use this command in a stored form sequence to automatically print the form
(as soon as all variable data has been supplied).
Syntax PAp ,[p ]
1 2
Parameters This table identifies the parameters for this format:
Parameters Details
p = Number of label sets. Can be variable data.
1
Accepted Values: 1 to 9999
p = Number of copies of Can be variable data.
2
the same labael Accepted Values: 1 to 9999
Sets the number of copies of each label (used in combination
with counters) to print multiple copies of the same label. This
value is only set when using counters.
Example •
FK"1"↵ : delete form named “1"
FS"1"↵ : start form store sequence
V00,10,N,"prompt:"↵ : define variable 00
V01,1,N,"prompt:"↵ : define variable 01
V02,4,N,"prompt:"↵ : define variable 02
A24,24,0,4,1,1,N,V00↵ : write a line of text including
variable
PAV01,V02↵ : print 1 label automatically
FE↵ : end form store sequence
FR"1"↵ : retrieve form “1"
?↵ : get variables
This Is Text↵ : data for V00
3↵ : data for V01= p1- number of
sets
2↵ : data for V02= p2 - number of
copies
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 135

EPL Commands 135
q
q
Set Label Width
Description Use this command to set the width of the printable area of the media.
Syntax qp
1
This table identifies the parameters for this format:
Parameters Details
p = The width of the The q command will cause the image buffer to reformat and
1
label measured in position to match the selected label width (p ).
1
dots.
Figure 5 • Image Buffer Positioning—Center Aligned Printers
(cid:51)(cid:85)(cid:76)(cid:81)(cid:87)(cid:3)(cid:43)(cid:72)(cid:68)(cid:71)
(cid:48)(cid:68)(cid:91)(cid:17)
(cid:51)(cid:85)(cid:76)(cid:81)(cid:87)(cid:68)(cid:69)(cid:79)(cid:72) (cid:48)(cid:68)(cid:91)(cid:17)
(cid:44)(cid:80)(cid:68)(cid:74)(cid:72)
(cid:44)(cid:80)(cid:68)(cid:74)(cid:72)
(cid:37)(cid:88)(cid:73)(cid:73)(cid:72)(cid:85) (cid:36)(cid:85)(cid:72)(cid:68)
(cid:37)(cid:88)(cid:73)(cid:73)(cid:72)(cid:85)
(cid:84)
(cid:38)(cid:72)(cid:81)(cid:87)(cid:72)(cid:85)(cid:3)(cid:36)(cid:79)(cid:76)(cid:74)(cid:81)(cid:80)(cid:72)(cid:81)(cid:87)
Figure 6 • Image buffer Positioning—Left Aligned Printers
(cid:51)(cid:85)(cid:76)(cid:81)(cid:87)(cid:3)(cid:43)(cid:72)(cid:68)(cid:71)
(cid:51)(cid:85)(cid:76)(cid:81)(cid:87)(cid:68)(cid:69)(cid:79)(cid:72) (cid:44)(cid:80)(cid:68)(cid:74)(cid:72)
(cid:52)
(cid:36)(cid:85)(cid:72)(cid:68) (cid:37)(cid:88)(cid:73)(cid:73)(cid:72)(cid:85)
(cid:84)
(cid:47)(cid:72)(cid:73)(cid:87)(cid:3)(cid:36)(cid:79)(cid:76)(cid:74)(cid:81)(cid:80)(cid:72)(cid:81)(cid:87)(cid:3)(cid:40)(cid:71)(cid:74)(cid:72)
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |  |  |  |  |  | Buffer Positioning—Center |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  | (cid:48)(cid:68)(cid:91)(cid:17)<br>(cid:44)(cid:80)(cid:68)(cid:74)(cid:72)<br>(cid:37)(cid:88)(cid:73)(cid:73)(cid:72)(cid:85) |  |  |  |  |  |  | (cid:51)(cid:85)(cid:76)(cid:81)(cid:87)(cid:3)(cid:43)(cid:72)(cid:68)(cid:71) |  |  |  |  |  |  |  |  |  |  |  |  |  | (cid:48)(cid:68)(cid:91)(cid:17)<br>(cid:44)(cid:80)(cid:68)(cid:74)(cid:72)<br>(cid:37)(cid:88)(cid:73)(cid:73)(cid:72)(cid:85)<br>(cid:87) |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  | (cid:44)<br>(cid:37) | (cid:48)(cid:68)<br>(cid:80)(cid:68)<br>(cid:88)(cid:73) | (cid:91)(cid:17)<br>(cid:74)(cid:72)<br>(cid:73)(cid:72)(cid:85) |  |  |  |  |  | (cid:51) | (cid:85)(cid:76)<br>(cid:36) | (cid:81)(cid:87)<br>(cid:85) | (cid:72) | (cid:68)(cid:69)<br>(cid:68) |  | (cid:79)(cid:72) |  |  |  |  | (cid:48)(cid:68)(cid:91)(cid:17)<br>(cid:44)(cid:80)(cid:68)(cid:74)(cid:72)<br>(cid:37)(cid:88)(cid:73)(cid:73)(cid:72)(cid:85) |
|  |  |  |  |  |  |  | (cid:84)<br>(cid:38)(cid:72)(cid:81)(cid:87)(cid:72)(cid:85)(cid:3)(cid:36)(cid:79)(cid:76)(cid:74)(cid:81)(cid:80)(cid:72)(cid:81) |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  | e 6 • Image buffer Positioni |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  | i |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  | (cid:51)(cid:85)(cid:76)(cid:81)(cid:87)(cid:3)(cid:43)(cid:72)<br>(cid:51)(cid:85)(cid:76)(cid:81)(cid:87)(cid:68)(cid:69)(cid:79)(cid:72)<br>(cid:36)(cid:85)(cid:72)(cid:68)<br>(cid:84)<br>(cid:47)(cid:72)(cid:73)(cid:87)(cid:3)(cid:36)(cid:79)(cid:76)(cid:74)(cid:81)(cid:80) | (cid:51)(cid:85)(cid:76)(cid:81)(cid:87)(cid:3)(cid:43)(cid:72) |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |


|  |  |  |  |  |  |  |  |  |  |  | (cid:44)(cid:80)(cid:68)(cid:74)(cid:72)<br>(cid:37)(cid:88)(cid:73)(cid:73)(cid:72)(cid:85) |  |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
|  |  | (cid:51) | (cid:85)(cid:76)<br>(cid:36) | (cid:81)(cid:87)<br>(cid:85) | (cid:68)(cid:69)<br>(cid:72)(cid:68) | (cid:79)(cid:72) |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |
|  |  |  |  |  |  |  |  |  |  |  |  |  |


---

## Pagina 136

136 EPL Commands
q
Notes •
For all EPL desktop printers, this command will automatically set the left margin according
to the following rules:
(print head width -label width) / 2
The q value affects the available print width. Minimizing the q value will maximize the print
length and print speed (double buffering).
If the R Command (Reference Point) is sent after this command, the image buffer will be
automatically reformatted to match the width of the print head and is offset by the R
command specified image buffer starting point, nullifying the q command.
Example •
q416↵ : sets label width to 416 dots
wide
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 137

EPL Commands 137
Q
Q
Set Form Length
Description Use this command to set the form and gap length or black line thickness when
using the transmissive (gap) sensor, black line sensor, or for setting the printer into the
continuous media print mode.
The Q command will cause the printer to recalculate and reformat image buffer.
Syntax Qp ,p ,[±p ]
1 2 3
Parameters This table identifies the parameters for this format:
Parameters Details
p = Label length Default Value: Set by the AutoSense of media
1
measured in dots Accepted Values: 0-65535
• Distance between edges of the label or black line marks.
• For continuous mode, the p 1 parameter sets the feed
distance between the end of one form and beginning of the
next.
p = Gap length or Accepted Values:
2
thickness of black 16-240 (dots) for 203 dpi printers
line
18-240 (dots) for 300dpi printers
Gap Mode
By default, the printer is in Gap mode and parameters are set
with the media AutoSense.
Black Line Mode
Set p to B plus black line thickness in dots. See the Gap
2
mode range.
Continuous Media Mode
Set p2 to 0 (zero)The transmissive (gap) sensor will beused to
detect the end of media.
p = Offset length Required for black line mode operation.
3
measured in dots Optional for Gap detect or continuous media modes. Use only
positive offset values.
AutoSense routine does not detect black line or continuous media.
All EPL2 printers have a transmissive (gap) sensor designed to detect the top of each label or
tag. It does this in one of two ways:
• Sensing through the label liner at the gap between labels.
• Looking through a hole (notch) in the tag.
Printers equipped with a black line sensor can determine the top of each label or tag by sensing
a “black line” preprinted on the media backing.
5/30/07 EPL Programming Guide 14245L-001 Rev. A

---

## Pagina 138

138 EPL Commands
Q
Sensor location is important when selecting the proper of label or tag type for printing. See the
printer’s user manual for specific information on alignment, adjustment, and position of the
transmissive (gap) or reflective (black line) sensors.
If the label size is not set properly, the printer may print off the edge of the label or tag and
onto the backing or platen roller. Repeated printing off the edge of the label can cause
excessive print head wear.
Maintain a minimum margin of 0.04 inches (1 mm) on all sides of the label.
Setting the label size to large can cause the printer to skip labels.
Example 1 • Standard Label
p
1
p
2
Where:
p1 = 20.mm (160 dots)
p2 = 3.0 mm (24 dots)
The Q command would be:
Q160,24↵
Example 2 • Butterfly Label
p
1
p
2
p
3
Where:
p1 = 12.5 mm (100 dots)
p2 = 3.0 mm (24 dots)
p3 = 3.0 mm (24 dots)
The Q command would be:
Q100,24+24↵
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


|  |
| --- |
|  |


|  |  |
| --- | --- |
|  |  |


|  |
| --- |
|  |
|  |


---

## Pagina 139

EPL Commands 139
Q
Example 3 • Black Line on Perforation
p
1
p
2
Perforation
p
3
Where:
p1 =
p2 =
p3 =
The Q command would be:
Q100,24+24↵
Example 4 • Black Line Between Perforation
p
1
Perforation p
2
p
3
Where:
p1 =
p2 =
p3 =
The Q command would be:
Q100,24+24↵
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


|  | p<br>1<br>p<br>2<br>p<br>3 |
| --- | --- |
|  |  |
|  |  |
|  |  |
| Perforation |  |
|  |  |
|  |  |


|  |  |
| --- | --- |
|  |  |


|  | p<br>1<br>p<br>2<br>p<br>3 |
| --- | --- |
|  |  |
|  |  |
| Perforation |  |
|  |  |
|  |  |
|  |  |
|  |  |
|  |  |


---

## Pagina 140

140 EPL Commands
r
r
Set Double Buffer Mode
Description Use this command to disable or reenable the double buffer image (label)
printing. The double buffer feature is a automatically tested and set by the q and Q commands.
Mobile printers, such as the TR 220, ignore this command and automatically set the printer to
single buffer mode.
Syntax rp
1
Parameters This table identifies the parameters for this format:
Parameters Details
p = Enable/disable Accepted Values:
1
double buffer mode N = Disable double buffer mode
Y = Re-enable the double buffer mode if the printer
memory supports the image buffer size set by Q and q
parameters
Note • The rN command must follow the q and Q
commands in a form (label) program.
Verify the image buffer status with the U command(s).
See Explanation of the Status Printout onpage34 for
a sample of the Dump Mode Printout.
14245L-001 Rev. A EPL Programming Guide 5/30/07

---

## Pagina 141

EPL Commands 141
R
R
Set Reference Point
Description Use this command to move the reference point for the X and Y axes. All
horizontal and vertical measurements in other commands use the setting for R as the origin for
measurements. Use the R command as an alternative to sending the q command to position
(center) labels that are narrower than the print head.
The R command interacts with image buffer setting, as follows:
• The R command forces the printer to use the full width of the print head as the width of the
image buffer. The R command overrides the q commands print width setting.
• Rotate the image buffer with the Z command to establish top and left margins (ZT) or the
bottom and right margins (ZB).
• When positioned correctly, prevents printing off two (2) edges of the label opposite the 0,0
reference point.
Syntax Rp ,p
1 2
Parameters This table identifies the parameters for this format:
Parameters Details
p =Horizontal (left) Horizontal (left) margin measured in
1
margin
dots.
p = Vertical (top) margin Vertical (top) margin measured in dots.
2
measured in dots.
Note •
• Use the Q and R commands together for the easiest method of positioning form elements
in the print image in left-aligned table top printers.
• Repeated printing off the edge of the label can cause excessive print head wear.
5/30/07 EPL Programming Guide 14245L-001 Rev. A

---

## Pagina 142

142 EPL Commands
S
S
Speed Select
Description Use this command to select the print speed.
Mobile printers, such as the TR 220, ignore this command and automatically set speed to
optimize battery use.
Syntax Sp
1
Parameters This table identifies the parameters for this format:
Parameters Details
p = Speed select value
1
Model Value Speed
2722 0 1.0 ips (25 mm/s)
2742
1 1.5 ips (37 mm/s)
3742
2 2.0 ips (50 mm/s)
3842
2824 1 1.5 ips (37 mm/s)
2844 2 2.0 ips (50 mm/s)
3 2.5 ips (63 mm/s)
4 3.5 ips (83 mm/s)
2443 (Orion) 1 1.5 ips (37 mm/s)
2 2.0 ips (50 mm/s)
3 2.5 ips (63 mm/s)
2746 2 2.0 ips (50 mm/s)
2746e
3 3.0 ips (50 mm/s)
2348
4 4.0 ips (50 mm/s)
5 5.0 ips (50 mm/s)
6 6.0 ips (50 mm/s)
2684 (Strata) 1 1.0 ips (25 mm/s)
2 2.0 ips (50 mm/s)
3 3.0 ips (50 mm/s)
4 4.0 ips (50 mm/s)
Example • This example selects 2 ips (50 mmps).
S2↵
14245L-001 Rev. A EPL Programming Guide 5/30/07

| Model | Value |
| --- | --- |
| 2722<br>2742<br>3742<br>3842 | 0 |
|  | 1 |
|  | 2 |
| 2824<br>2844 | 1 |
|  | 2 |
|  | 3 |
|  | 4 |
| 2443 (Orion) | 1 |
|  | 2 |
|  | 3 |
| 2746<br>2746e<br>2348 | 2 |
|  | 3 |
|  | 4 |
|  | 5 |
|  | 6 |
| 2684 (Strata) | 1 |
|  | 2 |
|  | 3 |
|  | 4 |


|  |  |
| --- | --- |
|  |  |


---

## Pagina 143

EPL Commands 143
TD
TD
Date Recall & Format Layout
Description Use this command to define the date format and print date data. The TD
variable is inserted within a Text or Bar Code command's DATA parameter to print the date.
The TD variable supports offsetting day by up to 253 days (see examples below for usage).
This command only works in printers equipped with the Real Time Clock time and date
option.
Power-Up Default Format - mn-dd-y4
Syntax TDp [|p |p
1 2 3]
Parameters This table identifies the parameters for this format:
Parameters Details
p = The parameters describe the format of the date display. At
1
lease one parameter must be supplied. Each parameter can be
p =
2 any of the acceptable values listed below.
p =
3
Value Description
y2 Year displayed as 2 digits (07)
y4 Year displayed as 4 digits (2007)
me Month displayed as 3 letters (JAN)
mn Month displayed as 2 digits (01)
dd Day displayed as 2 digits (15)
| = Separator character. Accepted Values: Any ASCII character value between 032
and 063. The separator character is printed between the
results of each of the supplied parameters.
Example • In this example, the current date is January 15, 2000.
TDy2/me/dd↵ : 00/JAN/15
TDdd-me-y4↵ : 15-JAN-2000
TDdd,mn,y4↵ : 15,01,2000
TDdd/mn/y2↵ : 15/09/00
A100,100,0,4,1,2,N,"Today is"TD↵: Today is 15/09/00
A100,200,0,4,1,2,N,"Next Week-"TD+07↵: Next Week-22/09/00
A100,300,0,4,1,2,N,"Next Month-"TD+30↵: Next Month-15/10/00
A100,400,0,4,1,2,N,"Two Months-"TD+61↵: Two Months-15/11/00
5/30/07 EPL Programming Guide 14245L-001 Rev. A

| Value |
| --- |
| y2 |
| y4 |
| me |
| mn |
| dd |


|  |  |
| --- | --- |
|  |  |


---

## Pagina 144

144 EPL Commands
TS
TS
Set Real Time Clock
Description Use this command to set the time and date in printers equipped with the Real
Time Clock option.
Syntax TSp ,p ,p ,p ,p ,p
1 2 3 4 5 6
Parameters This table identifies the parameters for this format:
Parameters Details
p = Month Accepted Values: 01–12
1
p = Day Accepted Values: 01–31
2
p = Year Value is equivalent to the last two digits of Year (e.g. 95)
3
Accepted Values:
90-99 (to indicate 1990-1999)
00-89 (to indicate 2000-2089)
p = Hour Shown in 24 hour format.
4
Accepted Values: 00–23
p = Minutes Accepted Values: 00–59
5
p = Seconds Accepted Values: 00–59
6
Example • In this example, the current date is January 15, 2000.
TS01,01,95,01,00,00↵ :sets the date to Jan. 1, 1995
and the time to 1:00 a.m.
TS12,31,01,15,31,00↵ : sets the date to Dec. 31, 2001
and the time to 3:31 p.m.
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 145

EPL Commands 145
TT
TT
Time Recall & Format Layout
Description Use this command to define the time format and print time data. The TT
variable is inserted within a Text or Bar Code command's DATA parameter to print the time.
This command works only in printers equipped with the Real Time Clock (RTC) time and date
option.
Syntax TTp1[|p2|p3][+]
Parameters This table identifies the parameters for this format:
Parameters Details
p , p , p = h, m, or s These parameters describe the format of the time display.
1 2 3
• At lease one parameter must be supplied.
• Each parameter can be any of the values h, m, or s and are
described below.
h = Hours displayed as 2 digits (e.g. 01)
m = Minutes displayed as 2 digits (e.g. 15)
s = Seconds displayed as 2 digits (e.g. 00)
Default Value: h:m:s (power-up default format)
+ = Enable 12 Hour Appending a + to the end of the command string selects 12
clock format hour clock mode. The times will display with an “AM” or
“PM” indicator. Default (no +) = 24 hour clock mode
| = Separator character The separator may be any ASCII character value between 032
and 063. The separator character is printed between the
results of each of the supplied parameters.
Example • If the current time is 1:25 p.m.:
TTh:m:s+↵ : 01:25:00PM
TTh,m↵ : 13,25
TTh+↵ : 01 PM
When printing the following:
N↵
TTh:m:s+↵
A100,500,0,4,1,2,N,"Current Time↵: "TT
A100,600,0,4,1,2,N,"+ 1Hr↵ "TT+60
A100,700,0,4,1,2,N,"+ 12hrs↵: "TT+720
P1↵
the result will be similar to this:
Current Time : 01:25:35PM
+1Hr : 02:25:35PM
+12Hrs : 01:25:35PM
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 146

146 EPL Commands
U
U
Print Configuration (General)
Description Use this command to print the current printer configuration for page mode
printing. The printout is the same the Dump Mode printout initiated by the printer’s AutoSense
routine. The printer does not enter Dump Mode. See Explanation of the Status Printout
onpage34 for a description of this printout.
Syntax U
Parameters There are no parameters for this format.
Example • The configuration label below is produced by this command:
U↵
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 147

EPL Commands 147
UA
UA
Enable Clear Label Counter Mode
Description This command sets the printer to clear (empty) the print buffer if a media out
condition is detected.
A power cycle, reset, or UB command will clear this setting.
Normal (default) operation for the printer is to resume printing if the empty roll is replaced
with new roll (or ribbon) and finish print any labels in the process of printing prior to a media
out condition, including batch print jobs.
Syntax UA
Parameters There are no parameters for this format.
Example •
UA↵
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 148

148 EPL Commands
UB
UB
Reset Label Counter Mode
Description Use this command to clear the UA command and restore the default setting to
allow the printer to resume printing a batch job if a paper empty occurs. The page mode
(EPL2) printer, by default, will resume printing if the empty roll is replaced with new roll (or
ribbon) and finish a batch print job.
Syntax UB
Parameters There are no parameters for this format.
Example •
UB↵
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 149

EPL Commands 149
UE
UE
External Font Information Inquiry
Description This command will cause the printer to send information about external fonts
currently stored in the printer back to the host.
The printer will send the number of external fonts stored and each font’s name, height and
direction, to the host through the RS-232 port.
Syntax UE
Parameters There are no parameters for this format.
Example •
UE↵
will produce:
### : number of external fonts
A,xxx,y : first font
... : A=fontname
... : xxx=font height in dots
... : y=direction (0=0°, 1=90°,
2=both)
A,xxx,y : last font
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 150

150 EPL Commands
UF
UF
Form Information Inquiry
Description This command will cause the printer to send information about forms currently
stored in the printer back to the host.
Syntax UF
Parameters There are no parameters for this format.
Example •
UF↵
will produce:
### : number of forms
FORMNAME1 : first form name
FORMNAME2 : second form name
...
FORMNAMEn : last form name
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 151

EPL Commands 151
UG
UG
Graphics Information Inquiry
Description This command will cause the printer to send information about graphics
currently stored in the printer back to the host.
Syntax UG
Parameters There are no parameters for this format.
Example •
UG↵
will produce:
### : number of graphics
GRAPHICNAME1 : first graphic name
GRAPHICNAME2 : second graphic name
...
GRAPHICNAMEn : last graphic name
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 152

152 EPL Commands
UI
UI
Host Prompts/Codepage Inquiry
Description This command will cause the printer to enable prompts to be sent to the host
and it will send the currently selected codepage to the host through the RS-232 port.
This command also disables software flow control (XON/XOFF). Hardware flow control is
not disabled (DTR/CTS). To restart software flow control a reset (^@ command) or power
must be recycled.
See also the I and U commands.
Syntax UIp ,p ,p
1 2 3
Parameters This table identifies the parameters for this format:
Parameters Details
The printer will send information about the currently selected code page back to the host in
the following format:
p Number of data bits.
1
p Code page.
2
p Country code.
3
Note • The KDU automatically sends this command each time power is applied.
Example •
UI↵
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 153

EPL Commands 153
UM
UM
Codepage & Memory Inquiry
Description This command will cause the printer to send to the host the currently selected
codepage and memory status through the RS-232 port.
This command also disables software flow control (XON/XOFF). Hardware flow control is
not disabled (DTR/CTS). To restart software flow control a reset (^@ command) or power
must be recycled.
See also the I, M, U, UI and UP commands.
Syntax UMp ,p ,p ,p ,p ,p ,p ,p
1 2 3 4 5 6 7 8
Parameters This table identifies the parameters for this format:
Parameters Details
p Image buffer size in KBytes.
1
p Form memory allocation size in KBytes.
2
p Form memory free in KBytes.
3
p Graphic memory allocation size in KBytes.
4
p Graphic memory free in KBytes.
5
p External font memory allocation size in KBytes.
6
p External font memory free in KBytes.
7
p Appends a response in the UI command data format. See the
8
UI command onpage152.
Example •
UM↵
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 154

154 EPL Commands
UN
UN
Disable Error Reporting
Description Cancels US command.
Syntax UN
Parameters There are no parameters for this format.
14245L-001 Rev. A EPL Programming Guide 5/30/07

---

## Pagina 155

EPL Commands 155
UP
UP
Codepage & Memory Inquiry/Print
Description This command will cause the printer to print and send the currently selected
codepage and memory status to the host through the RS-232 port.
This command also disables software flow control (XON/XOFF). Hardware flow control is
not disabled (DTR/CTS). To restart software flow control a reset (^@ command) or power
must be recycled.
See also the I, M, U, UI and UM commands.
Syntax UP
The printer will send information about the currently selected code page and memory
status back to the host followed by printing the current printer configuration. For an
example of the configuration printout, see the U command.
The format of data sent to the host is as follows:
UPp ,p ,p ,p ,p ,p ,p ,p ,p
1 2 3 4 5 6 7 8 9
Parameters This table identifies the parameters for this format:
Parameters Details
p Image buffer size in KBytes.
1
p Form memory allocation size in KBytes.
2
p Form memory free in KBytes.
3
p Graphic memory allocation size in KBytes.
4
p Graphic memory free in KBytes.
5
p External font memory allocation size in KBytes.
6
p External font memory free in KBytes.
7
p Appends a response in the UI command data format. See the
8
UI command onpage152.
Example •
Up↵
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 156

156 EPL Commands
UQ
UQ
Configuration Inquiry
Description Use this command to send the printer configuration information back to the
host via the serial port.
The printer will send the printer configuration, line by line, in ASCII to the host through the
RS-232 port. The information matches the configuration information printed in final phase of
the printer’s AutoSense routine, the Dump Mode Printout or the U command printout.
The information and number of lines of data sent by the printer will vary from printer to printer
depending upon the type of printer and options installed.
Syntax UQ
Parameters There are no parameters for this format.
Example •
UQ↵
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 157

EPL Commands 157
US
US
Enable Error Reporting
Description Use this command to enable the printer’s status reporting feature.
• Serial Port
• If an error occurs, the printer will send a NACK(0x15), followed by the error number,
to the computer.
• If no errors occur, the printer will echo ACK(0x6) after each label is printed or
removed if in dispense (peel) mode.
• If paper or ribbon empty occurs, the printer will send, through the serial port, a”-07”
and “Pnnn” where nnn is the number of labels remaining to print.
• Parallel Port
• If an error occurs, the printer will print the error number and the printer’s indicator(s)
LED will indicate an error condition. See the individual printer’s user manual for
details.
• Mobile Printers Only (TR220)
• Additionally enables command error reporting via printer’s status indicator. The
indicator is turned off by default for this printer only.
Note • The printer’s default setting is disabled error reporting.
If enabled, use the UN command to disable error reporting.
Syntax US[p ]
1
5/30/07 EPL Programming Guide 14245L-001 Rev. A

---

## Pagina 158

158 EPL Commands
US
Parameters This table identifies the parameters for this format:
Parameters Details
p = 1 (Optional If no errors occur, the printer will echo ACK(0x6) after each
1
Parameter) label that is successfully printed/dispensed.
Code Error/Status Description
00 No Error
01 Syntax Error
02 Object Exceeded Label Border
03 Bar Code Data Length Error (e.g.: EAN-13 is a 12 or 1 digit only)
04 Insufficient Memory to Store Data
05 Memory Configuration Error
06 RS-232 Interface Error
07 Paper or Ribbon Empty
08 Duplicate Name: Form, Graphic or Soft Font
09 Name Not Found: Form, Graphic or Soft Font
10 Not in Data Entry Mode (See ? Command)
11 Printhead Up (Open)
12 Pause Mode or Paused in Peel Mode
13 Mobile Printers: Print head too hot
14 Mobile Printers: Motor too hot
15 Mobile Printers: Battery low warning ( 40%)
16 Mobile Printers: Battery low limit ( 20%)
50 Printer Busy - Processing Print Job
84 Media Error or Blackline not detected
93 PDF-417 coded data too large to fit in bar code
14245L-001 Rev. A EPL Programming Guide 5/30/07

---

## Pagina 159

EPL Commands 159
UT
UT
Enable Alternate Error Reporting
Description Use this command to enable the printer’s status alternate reporting feature.
• Serial Port
• If an error occurs, the printer will send a NACK(0x15), followed by the error number,
to the computer.
• If no errors occur, the printer will echo ACK(0x6) after last line of the current label
has been rasterized.
• The printer will send a DLE(0x10) when the label is dispensed.
• If paper or ribbon empty occurs, the printer will send, through the serial port, a ”-07”,
“Pnnn” where nnn is the number of labels remaining to print. (Same as US1
command)
• The UT command (when compared to the US1 command) then adds a "Lyyyyy" to
the end, where yyyyy is the number of unprinted raster lines. (07PnnnLyyyyy)
• Parallel Port
• If an error occurs, the printer will print the error number and the printer’s indicator(s)
LED will indicate an error condition. See the individual printer’s user manual for
details.
• Mobile Printers Only (TR220)
• Additionally enables command error reporting via printer’s status indicator. The
indicator is turned off by default for this printer only.
Note • The printer’s default setting is disabled error reporting.
If enabled, use the UN command to disable error reporting.
Syntax UT
Parameters There are no parameters for this format.
5/30/07 EPL Programming Guide 14245L-001 Rev. A

---

## Pagina 160

160 EPL Commands
U%
U%
Host Prompts/Motor Temperature
Description This command will cause the printer to send to the host the motor temperature
status through the mobile printer’s serial port. This command applies to Mobile Printers Only
(TR 220).
The printer will send motor temperature in 2ºC increments to the host via the serial port. The
printer uses the data format of NNdeg C, where NN equals the motor temperature.
Range Reported (Degrees Celsius): 24NN60
Syntax U%
Parameters There are no parameters for this format.
Example • In this example, the command is sent to the printer and the printer responds with
a motor temperature of 24 deg C.
U%↵
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 161

EPL Commands 161
U$
U$
Host Prompts/Battery Status
Description This command will cause the printer to send to the host the battery charge
status. This command applies to EPL Mobile Printers Only (TR 220).
The printer will send information about battery charge status in increments of 10 percent. The
printer uses the data format of VccNNN%, where NNN represents the battery’s charge level.
Note • EPL Mobile printers suspend all new printing operations, including printer to host
communication, until the battery charge level is greater than 10%.
Print jobs or forms (single label or batch operations) will continue processing until finished.
The mobile printer will accept new commands and print after the battey charge is greater than
10%.
Syntax U$
Parameters There are no parameters for this format.
Example • In this example, the command is sent to the printer and the printer responds with
Vcc90%.
U$↵
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 162

162 EPL Commands
V
V
Define Variable
Description Use this command to define variable data for the text and bar code data fields
in stored forms. Variable data can be combined with fixed data or other data types (counter,
date, etc..) in text or bar code data fields.
Use this command in forms that require unique data on each label. When initializing variables:
• They must be defined in order (e.g. V00 first, V01 second...)
• They must be the next entries after the FS"FORMNAME" command.
• They must be located before any counter variables.
• Variables must not contain the NULL character (0 dec.;00 hex.). The NULL character is
an illegal text character.
To print the contents of the variable, the variable number is referenced in the “DATA” field of
the A (ASCII text) or B (Bar Code) commands.
The field justification parameter effects the way the variable will be printed. When L of R are
selected, the variable value will be printed left or right justified in an area with a width defined
by p parameter.
2
Syntax Vp ,p ,p ,”[-]PROMPT”
1 2 3
Parameters This table identifies the parameters for this format:
Parameters Details
p = Variable number Variable (reference) numbers are sequential and must be input
1
into a form in ascending order.
Accepted Values: 00 to 99
p = Maximum number of This is the maximum number of characters allowed in the
2
characters variable field.
Accepted Values: 1 to 99
p = Field Justification Accepted Values:
3
L = Left
R = Right
C = Center
N = No Justification
Note • Right and center justification does not apply to
soft fonts.
14245L-001 Rev. A EPL Programming Guide 5/30/07

---

## Pagina 163

EPL Commands 163
V
Parameters Details
PROMPT = ASCII text An ASCII text field that will be transmitted to the host (via
field the serial interface) each time this command is executed.
Use the prompt to ask for a value to be entered for the
variable.
[-] = KDU Options Having the first character of the prompt a single minus sign
will cause the prompt to display only once after form
retrieval.
The KDU allows a maximum of 40 characters for entry into a
variable data field and display.
Accepted Values: 1 to 40 for KDU Range (p ), but not to
2
exceed a total of 1500 bytes for all variables.
Example •
V00,15,N,"Enter Product Name:"↵
Note • The maximum amount of data stored as variable data, including counter variables and
data reference overhead can not exceed 1500 bytes.
KDU Support: Internally stored variable data fields are reset after cycling printer power or
sending a reset command or pressing the Cancel.
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 164

164 EPL Commands
W
W
Windows Mode
Description This command is used to disable/re-enable the Windows command mode.
When enabled, the printer will accept Windows mode escape sequences to print data. When
disabled, escape sequences will be ignored.
The Windows mode escape sequences are only used by the optional Windows printer driver.
When working with a main frame or other non-Windows host, this mode can be disabled to
prevent erratic operation.
Syntax Wp
1
Parameters This table identifies the parameters for this format:
Parameters Details
p = Windows Mode Accepted Values:
1
Enabled Y = enabled
N = disabled
Default Value: N (disabled)
14245L-001 Rev. A EPL Programming Guide 5/30/07

---

## Pagina 165

EPL Commands 165
xa
xa
AutoSense
Description This command is used to have the printer detect the label and gap length and
set the sensor levels. This command will not enter into the Dump mode or print the printer
configuration label.
Syntax xa
Parameters There are no parameters for this format.
Example • In this example, the printer will feed labels and measure the labels to set the Q
values (label & gap length) and the sensor levels. The printer is at Top of Form (TOF) and
ready to print.
xa↵
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 166

166 EPL Commands
X
X
Box Draw
Description Use this command to draw a box shape.
Syntax Xp ,p ,p ,p ,p
1 2 3 4 5
Parameters This table identifies the parameters for this format:
Parameters Details
p = Horizontal start Horizontal start position (X) in dots.
1
position
p = Vertical start position Vertical start position (Y) in dots.
2
p = Line thickness Line thickness in dots.
3
p = Horizontal end Horizontal end position (X) in dots.
4
position
p = Vertical end position Vertical end position (Y) in dots.
5
Example • This example will produce the results shown below.
N↵
X50,200,5,400,20↵
X200,50,10,20,400↵
P1↵
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 167

EPL Commands 167
Y
Y
Serial Port Setup
Description Use this command to establish the serial port communication parameters. After
receiving this command, the printer will automatically reset, enabling the new rate.
To send commands and data to the printer, the host’s serial port parameters must match the
printer’s serial port parameters. Verify the printer’s configuration settings with the
AutoSense/Dump Mode Printout, see the printer’s user manual for details. The printer’s
default serial port parameters are:
9600 baud,No Parity, 8Data Bits, 1 Stop Bit
Change the printer’s serial port parameters with the Y command after communication has been
established with the host. The host parameters must then be changed to resume
communication.
Syntax Yp ,p ,p ,p
1 2 3 4
Parameters This table identifies the parameters for this format:
Parameters Details
p = Baud rate
1
p Description
1
38 38,400 baud (38K)
19 19,200 baud
96 9,600 baud
48 4,800 baud
24 2,400 baud
12 1,200 baud
p = Parity Accepted Values:
2
O = Odd parity
E = Even parity
N = No parity
p = # Data bits Accepted Values:
3
7 = Seven data bits
8 = Eight data bits
p = # Stop bits Accepted Values:
4
1 = One stop bit
2 = Two stop bits
Example • This example sets 19,200 baud, odd parity, 7 data bits and 1 stop bit.
Y19,O,7,1↵
5/30/07 EPL Programming Guide 14245L-001 Rev. A

| p<br>1 |
| --- |
| 38 |
| 19 |
| 96 |
| 48 |
| 24 |
| 12 |


|  |  |
| --- | --- |
|  |  |


---

## Pagina 168

168 EPL Commands
Z
Z
Print Direction
Description Use this command to select the print orientation.
Syntax Zp
1
Parameters This table identifies the parameters for this format:
Parameters Details
p = Print orientation Accepted Values:
1
T = Printing from top of image buffer.
B = Printing from bottom of image buffer.
Default Value: T
Note • The top of the image buffer prints first and is viewed by the operator as printing
upside down.
14245L-001 Rev. A EPL Programming Guide 5/30/07

---

## Pagina 169

EPL Commands 169
Z
Example • This example will produce the results shown below.
N↵
ZT↵
GG10,10,"WORLD"↵
A10,200,0,3,1,1,N,"This Graphic Was Printed With
Orientation Set To ZT"↵
P1↵
N↵
ZB↵
GG10,10,"WORLD"↵
A10,200,0,3,1,1,N,"This Graphic Was Printed With
Orientation Set To ZB"↵
P1↵
5/30/07 EPL Programming Guide 14245L-001 Rev. A
noitceriD
tnirP

|  |  |
| --- | --- |
|  |  |


|  |
| --- |
|  |
|  |
|  |
|  |


---

## Pagina 170

170 EPL Commands
?
?
Download Variables
Description This command signals the printer to “fill-in” variable or counter “prompt” data
field.
The host system can send data representing variables and/or counters to the printer after a
stored form containing variables and/or counters has been retrieved.
Syntax ?
DATA
The amount of data following the question mark line must match exactly the order and total
number of variables and/or counters for that specific form.
Data must be entered, as follows:
• Each DATA line represents a variable or counter data field fill-in.
• Variables in ascending order (e.g. V00 first, V01 second...)
• Counters in ascending order following Variables (e.g. C0 first, C1 second...)
Parameters There are no parameters for this format.
Example •
FK"form1"↵ :delete form “form1"
FS"form1"↵ :begins the form store
V00,15,N,"Enter Part Name:"↵
V01,5,N,"Enter Quantity:"↵
A50,10,0,3,1,1,N,V00↵
A50,400,0,3,1,1,N,"Quantity: “V01↵
FE↵ :ends form store sequence
FR"form1"↵ :retrieve for “form1"
?↵ :variables follow
Screws↵ :first variable
235↵ :second variable
P1↵ :print one label
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 171

EPL Commands 171
^@
^@
Reset Printer
Description This command is used to reset the printer.
Syntax ^@
where ^ is 94 decimal
This command emulates Power Off and then Power On; thus reinitializing the printer.
• The reset command is unavailable during the operation of storing PCX graphics, soft fonts
or while the printer is in dump mode.
• The reset command cannot be used in a stored form.
• The reset command can be sent to the printer during all other printing operations.
• The printer will ignore all commands sent while the reset command is executing, up to 2
seconds.
Parameters There are no parameters for this format.
Example • This example causes the printer to reset.
^@↵
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 172

172 EPL Commands
^default
^default
Set Printer to Factory Defaults
Description Use this command to return the printer to its default configuration.
The ^default command resets the density, speed, sensors, image buffer parameters,
character code page (including re-mapped characters), options, feed button behaviors, gap
mode media sensing, serial interface configuration, error reporting and line mode
configuration defaults.
This command is intended for troubleshooting and by service organizations. Do not use this
command in regular programming! Do not use this command to initialize the printer! This
overwrites all stored parameters. The programmer should always minimize writing to the non-
volatile 'flash' printer memory.
Supported by firmware versions 4.30 and above.
Syntax ^default
Parameters There are no parameters for this format.
14245L-001 Rev. A EPL Programming Guide 5/30/07

---

## Pagina 173

EPL Commands 173
^ee
^ee
Error Report - Immediate
Description Use this command to get printer errors and status reports immediately. The
^ee command must be sent via the USB and RS-232 serial interface.
Mobile printers, such as the TR 220, ignore this command.
The printer will report 4 bytes back to host in the following format:
XX<CR><LF>
XX = Error/Status code
<CR>= Carriage Return (ASCII 13 dec.)
<LF>= Line Feed (ASCII 10 dec.)
Syntax ^ee
Parameters There are no parameters for this format.
Code Error/Status Description
00 No Error
01 Syntax Error
02 Object Exceeded Label Border
03 Bar Code Data Length Error (e.g.: EAN-13 is a 12 or 1 digit only)
04 Insufficient Memory to Store Data
05 Memory Configuration Error
06 RS-232 Interface Error
07 Paper or Ribbon Empty
08 Duplicate Name: Form, Graphic or Soft Font
09 Name Not Found: Form, Graphic or Soft Font
10 Not in Data Entry Mode (See ? Command)
11 Printhead Up (Open)
12 Pause Mode or Paused in Peel Mode
50 Printer Busy - Processing Print Job
80* Undefined
81* Cutter Jammed or Not Installed
82* AutoSense or Sensor Failure
83* Illegal Interrupt occurred
84* Excessive Media Feeding
* Requires Intervention: Press Feed or Reset (^@ command)
5/30/07 EPL Programming Guide 14245L-001 Rev. A

---

## Pagina 174

174 EPL Commands
;
;
Code Comment Line
Description This command signals the printer to ignore the following data. All data
between the line initiating semicolon character (;) and the next line feed (LF) character (which
terminates all command lines) will be ignored.
Supported by firmware versions 4.30 and above.
Syntax ; Comment Data
Example • The phrase “This is used for xxxXXX” is ignored by the printer.
; This is used for xxxXXX↵
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 175

5/30/07 EPL Programming Guide 14245L-001 Rev. A
4
SGD Printer Setting
Commands
This chapter provides a high-level overview of printer setting Set/Get/Do (SGD)
commands.
SGD commands are available in printers with firmware versions E53.15.x or later.
Important • These are important points to note when using EPL, ZPL, and SGD commands:
• SGD commands are case-sensitive.
• EPL, ZPL, and SGD commands should be sent to the printer as separate files.
• Certain settings can be controlled by EPL, ZPL, and SGD. Configuration changes made in
EPL or ZPL can affect configuration changes made in SGD.
• Changes made with one command type (EPL, ZPL, and SGD) will affect the data returned
to the host in response to EPL, ZPL, and getvar commands. The command type (EPL,
ZPL, or SGD) that was sent last determines the current setting.
• Some RF cards do not support all of the SGD commands.

|  |
| --- |
|  |


---

## Pagina 176

176 SGD Printer Setting Commands
Overview
14245L-001 Rev. A EPL Programming Guide 5/30/07
Overview
This section describes how and why to use the Set/Get/Do (SGD) commands. It also
provides an example of a typical command structure.
Note • SGD commands must be terminated by a carriage return or a space and line feed.
SGD commands are commands that allow you to configure all printers with firmware versions
E53.15.x or later. The printer performs the specified function immediately after receiving the
command. The commands are:
(cid:129) setvar
(cid:129) getvar
(cid:129) do
setvar Command
Setvar commands:
• are used to configure printer settings to specific values by setting them in the printer
• must be terminated by a space character or a CR/ LF (0x0D, 0x0A)
Important • The setvar command and attributes must be specified in lower case.
getvar Command
Getvar commands:
• are used to get the current value of the printer settings
• must be terminated by a space character or CR/LF (0x0D, 0x0A)
The printer responds with the printer setting of “?” if:
• the printer setting does not exist (usually due to incorrect spelling of the printer setting)
• it has not been configured yet
Important • The printer settings and attributes must be specified in lower case.

---

## Pagina 177

SGD Printer Setting Commands 177
Overview
5/30/07 EPL Programming Guide 14245L-001 Rev. A
do Command
Do commands:
• are used to instruct the printer to perform predefined actions
• must be terminated by a space character or a CR/LF (0x0D, 0x0A)
Some Do commands require additional settings which must be enclosed in double quotes.
Important • The values must be specified in lower case.

---

## Pagina 178

178 SGD Printer Setting Commands
Command Structure
14245L-001 Rev. A EPL Programming Guide 5/30/07
Command Structure
It is important to understand the structure of the command and its components. A command
structure illustration is provided for each command in this guide.
Example • This is an example of a command structure illustration:
! U1 setvar "ip.addr" "value"
1 2 3
1 Command—always preceded with an exclamation point (!) and must be specified in
lower case. A space resides between the !and U1 and between U1 and the command
(setvar or getvar).
2 Attribute—always in double quotes and must be specified in lower case.
3 Chosen value—always in double quotes. Only applicable for setvar and do.
This command must be terminated by a space character or a CR/ LF (0x0D, 0x0A).
Note • Some RF cards do not support all of the SGD commands.
How to Send Multiple SGD Commands
For any getvar, setvar, or do command, if you issue the syntax without the "1" and use
the END command followed by a space, multiple SGD commands are sent simultaneously.
Example • This syntax shows how you can send multiple getvar commands:
1 ! U getvar "ip.telnet.enable"
getvar "ip.dhcp.enable"
2
getvar "ip.dhcp.cid_prefix"
3 END
1 The command portion of the string does not use the "1" after the "! U".
2 Commands issued after the first command do not require the "! U".
3 The string of commands is terminated by the word "END" with a space after the
word, and by a carriage return/ line feed.

|  |  |
| --- | --- |
|  |  |


|  |  |
| --- | --- |
|  |  |


---

## Pagina 179

SGD Printer Settings 179
appl.bootblock
appl.bootblock
Description This command refers to the bootblock version. On the configuration label, the
bootblock number is identified as the hardware ID. This command is only supported on Zebra
ZM400/ZM600™ printers.
Type getvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage178.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the command for this format:
Commands Details
getvar This command returns the bootblock version number that appears on the
configuration label .
Format: ! U1 getvar "appl.bootblock"
Example • In this example, the getvar returns the bootblock version number.
! U1 getvar "appl.bootblock"
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 180

180 SGD Printer Settings
appl.name
appl.name
Description This command refers to the printer’s firmware version. This command is only
supported on Zebra ZM400/ZM600™ printers.
Type getvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage178.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the command for this format:
Commands Details
getvar This command returns the printer’s firmware version.
Format: ! U1 getvar "appl.name"
Example • In this example, the getvar returns the printer’s firmware version.
! U1 getvar "appl.name"
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 181

SGD Printer Settings 181
device.languages
device.languages
Description This command identifies the programming language that the printer is currently
using. This command is only supported on Zebra ZM400/ZM600™ printers.
Type getvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage178.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the command for this format:
Commands Details
getvar This command retrieves the programming language that the printer is
currently using. Table3 shows the possible response values.
Format: ! U1 getvar "device.languages"
Example • In this example, the getvar result is the current programming language that the
printer is using.
! U1 getvar "device.languages"
Table 3 • Programming Languages
ZPL (Zebra Programming Language)
EPL (Eltron Programming Language)
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 182

182 SGD Printer Settings
ip.active_network
ip.active_network
Description This command displays if the printer is actively connected to wireless, external
wired, or internal wired. This command is only supported on Zebra ZM400/ZM600™ and
S4M™ printers.
Type getvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage178.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with what the printer is
currently connected to internal wired, wireless, external wired, or
unknown. Table4 provides details on the potential return values.
Format: ! U1 getvar "ip.active_network"
Example • In this example, the getvar will return the current active network the printer is
connected to.
! U1 getvar "ip.active_network"
Table 4 • Printer Responses
Return Values Details
“internal wired” This is the return value when an internal wired device is detected.
“wireless” This is the return value when a wireless device is detected.
“external wired” This is the return value when an external wired device is detected.
“unknown” This is the return value:
• if the printer has not established a network connection on any
of the devices
• if you don't have any of the network devices plugged in
• if the printer is trying to establish a connection (for example,
when wireless is going through the association process)
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 183

SGD Printer Settings 183
ip.ftp.enable
ip.ftp.enable
Description This printer setting refers to the FTP protocol setting. This command tells the
printer to turn FTP on or off.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage178.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the FTP status.
Format: ! U1 getvar "ip.ftp.enable"
setvar This command instructs the printer to turn FTP on or off.
Format: ! U1 setvar "ip.ftp.enable" "value"
Values:
"off" = disables FTP
"on" = enables FTP
Default: "on"
Example • This setvar example shows the FTP status set to "on".
! U1 setvar "ip.ftp.enable" "on"
When the setvar value is set to "on", the getvar result is that the FTP status is "on".
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 184

184 SGD Printer Settings
ip.http.enable
ip.http.enable
Description This printer setting refers to the HTTP protocol/web server setting.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage178.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the HTTP status.
Format: ! U1 getvar "ip.http.enable"
setvar This command instructs the printer to change HTTP to on or off.
Format: ! U1 setvar "ip.http.enable" "value"
Values:
"off" = disables HTTP protocol
"on" = enables HTTP protocol
Default: "on"
Example • This setvar example shows the value set to "on".
! U1 setvar "ip.http.enable" "on"
When the setvar value is set to "on", the getvar result is "on".
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 185

SGD Printer Settings 185
ip.lpd.enable
ip.lpd.enable
Description This printer setting refers to the LPD (Line Printer Daemon) protocol setting.
Type getvar; setvar
Important • LPD communications from the host should be directed to port 515.
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage178.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the LPD status.
Format: ! U1 getvar "ip.lpd.enable"
setvar This command instructs the printer to turn LPD on or off.
Format: ! U1 setvar "ip.lpd.enable" "value"
Values:
"off" = disables LPD protocol
"on" = enables LPD protocol
Default: "on"
Example • This setvar example shows the value set to "on".
! U1 setvar "ip.lpd.enable" "on"
When the setvar value is set to "on", the getvar result is "on".
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 186

186 SGD Printer Settings
ip.pop3.enable
ip.pop3.enable
Description This printer setting determines if the printer queries a POP3 mailbox for mail.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage178.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the POP3 status.
Format: ! U1 getvar "ip.pop3.enable"
setvar This command instructs the printer to turn POP3 on or off.
Format: ! U1 setvar "ip.pop3.enable" "value"
Values:
"off" = disables POP3
"on" = enables POP3
Default: "on"
Example • This setvar example shows the value set to "on".
! U1 setvar "ip.pop3.enable" "on"
When the setvar value is set to "on", the getvar result is "on".
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 187

SGD Printer Settings 187
ip.pop3.password
ip.pop3.password
Description This printer setting refers to the POP3 mailbox password. This only applies if
"ip.pop3.enable" is set to on.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage178.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the POP3 password.
Format: ! U1 getvar "ip.pop3.password"
For protection a single "*" prints.
setvar This command instructs the printer to change the POP3 password.
Format: ! U1 setvar "ip.pop3.password" "value"
Values: A maximum of 20 alphanumeric characters
Default: " "
Example • This setvar example shows the value set to "password".
! U1 setvar "ip.pop3.password" "password"
When the setvar value is set to "password", the getvar result is "*".
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 188

188 SGD Printer Settings
ip.pop3.poll
ip.pop3.poll
Description This printer setting refers to how frequent (in seconds) the printer queries a
POP3 mailbox for new mail. This only applies if the "ip.pop3.enable" is set to on.
Type getvar; setvar
Note • A poll value of less then thirty seconds is not recommended. The printer is
unresponsive for several seconds when polling for email depending on data transfer time
from the server to the printer.
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage178.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the POP3 poll
frequency (in seconds).
Format: ! U1 getvar "ip.pop3.poll"
setvar This command instructs the printer to change the POP3 poll interval. A
value of "0" causes the printer to only query the POP3 mailbox one time,
on printer power up, or following a network reset.
Format: ! U1 setvar "ip.pop3.poll" "value"
Values: "0" through "65535"
Default: "0"
Example • This setvar example shows the value set to "0".
! U1 setvar "ip.pop3.poll" "0"
When the setvar value is set to "0", the getvar result is "0".
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 189

SGD Printer Settings 189
ip.pop3.server_addr
ip.pop3.server_addr
Description This printer setting refers to the POP3 server IP address that the printer contacts
when checking for new mail. This only applies if "ip.pop3.enable" is set to on.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage178.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the POP3 server
address.
Format: ! U1 getvar "ip.pop3.server_addr"
setvar This command instructs the printer to change the POP3 server address.
Format: ! U1 setvar "ip.pop3.server_addr" "value"
Values: Any valid POP3 server address
Default: "0.0.0.0"
Example • This setvar example shows the value set to "10.3.5.10".
! U1 setvar "ip.pop3.server_addr" "10.3.5.10"
When the setvar value is set to "10.3.5.10", the getvar result is "10.3.5.10".
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 190

190 SGD Printer Settings
ip.pop3.username
ip.pop3.username
Description This printer setting refers to the POP3 user name. This only applies if the
"ip.pop3.enable" is set to on.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage178.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the POP3 user name.
Format: ! U1 getvar "ip.pop3.username"
setvar This command instructs the printer to change the POP3 user name.
Format: ! U1 setvar "ip.pop3.username" "value"
Values: A maximum of 20 alphanumeric characters
Default: " "
Example • This setvar example shows the value set to "user".
! U1 setvar "ip.pop3.username" "user"
When the setvar value is set to "user", the getvar result is "user".
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 191

SGD Printer Settings 191
ip.primary_network
ip.primary_network
Description This command allows you to set the primary network to either wired or
wireless. This command is only supported on Zebra ZM400/ZM600™ and S4M™ printers.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage178.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the name of the current
primary network device.
Format: ! U1 getvar "ip.primary_network"
setvar This command instructs the printer to set the current network device.
Format: ! U1 setvar "ip.primary_network" "value"
Values:
1 = wired
2 = wireless
Default: "wired"
Example • This setvar example shows the value set to "wired".
! U1 setvar "ip.primary_network" "wired"
What the setvar value is set to is the getvar result. In this example, the getvar result is
"wired".
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 192

192 SGD Printer Settings
ip.smtp.domain
ip.smtp.domain
Description This printer setting refers to the domain name used by the printer in sending
email with respect to the SMTP server.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage178.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to return the SMTP domain name.
Format: ! U1 getvar "ip.smtp.domain"
setvar This command instructs the printer to change the SMTP domain name.
Format: ! U1 setvar "ip.smtp.domain" "value"
Values: A maximum of 24 alphanumeric characters
Default: "ZBRPrintServer"
Example • This setvar example shows the value set to "ZBRPrintServer.com".
! U1 setvar "ip.smtp.domain" "ZBRPrintServer.com"
When the setvar value is set to "ZBRPrintServer.com", the getvar result is
"ZBRPrintServer.com".
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 193

SGD Printer Settings 193
ip.smtp.enable
ip.smtp.enable
Description This printer setting refers to the SMTP protocol.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage178.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to return the SMTP status.
Format: ! U1 getvar "ip.smtp.enable"
setvar This command instructs the printer to turn SMTP on or off.
Format: ! U1 setvar "ip.smtp.enable" "value"
Values:
"off" = disables SMTP
"on" = enables SMTP
Default: "on"
Example • This setvar example shows the value set to "on".
! U1 setvar "ip.smtp.enable" "on"
When the setvar value is set to "on", the getvar result is "on".
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 194

194 SGD Printer Settings
ip.smtp.server_addr
ip.smtp.server_addr
Description This printer setting refers to the IP address of the SMTP server used for sending
email.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage178.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the current SMTP
server address.
Format: ! U1 getvar "ip.smtp.server_addr"
setvar This command instructs the printer to change the SMTP server address.
Format: ! U1 setvar "ip.smtp.server_addr" "value"
Values: Any valid IP address.
Default: 0.0.0.0
Example • This setvar example shows the value set to 10.10.10.10.
! U1 setvar "ip.smtp.server_addr" "10.10.10.10"
When the setvar value is set to "10.10.10.10", the getvar result is
"10.10.10.10".
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 195

SGD Printer Settings 195
ip.snmp.get_community_name
ip.snmp.get_community_name
Description This printer setting is used when making SNMP queries. The SNMP client
must supply the get community name that matches the printer’s get community name in order
to query any SNMP data.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage178.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to get the SNMP get community name
string.
Format: ! U1 getvar "ip.snmp.get_community_name"
For protection a single "*" prints.
setvar This command instructs the printer to set the SNMP get community name
string.
Format:
! U1 setvar "ip.snmp.get_community_name" "value"
Values: A maximum of 19 alphanumeric characters.
Default: "public"
Example • This setvar example shows the value set to "public".
! U1 setvar "ip.snmp.get_community_name" "public"
When the setvar value is set to "public", the getvar result is "*".
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 196

196 SGD Printer Settings
ip.snmp.set_community_name
ip.snmp.set_community_name
Description This printer setting is used when changing SNMP data remotely. To alter any
SNMP data, the SNMP client must supply the set community name that matches the printer’s
set community name.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage178.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to return the printer’s SNMP set
community name string.
Format: ! U1 getvar "ip.snmp.set_community_name"
For protection a single "*" returns.
setvar This command instructs the printer to set the SNMP set community name
string.
Format: ! U1 setvar "ip.snmp.set_community_name"
"value"
Values: A maximum of 19 alphanumeric characters
Default: "public"
Example • This setvar example shows the value set to "public".
! U1 setvar "ip.snmp.set_community_name" "public"
When the setvar value is set to "public", the getvar result is "*".
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 197

SGD Printer Settings 197
ip.telnet.enable
ip.telnet.enable
Description This printer setting refers to the TELNET (port 23) protocol.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage178.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the TELNET status.
Format: ! U1 getvar "ip.telnet.enable"
setvar This command instructs the printer to turn TELNET on or off.
Format: ! U1 setvar "ip.telnet.enable" "value"
Values:
"off" = disables telnet protocol
"on" = enables telnet protocol
Default: "on"
Example • This setvar example shows the value set to "on".
! U1 setvar "ip.telnet.enable" "on"
When the setvar value is set to "on", the getvar result is "on".
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 198

198 SGD Printer Settings
ip.snmp.enable
ip.snmp.enable
Description This printer setting refers to the SNMP protocol.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage178.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the SNMP status.
Format: ! U1 getvar "ip.snmp.enable"
setvar This command instructs the printer to enable or disable the SNMP
protocol.
Format: ! U1 setvar "ip.snmp.enable" "value"
Values:
"on" = enable the SNMP protocol
"off" = disable the SNMP protocol
Default: "on"
Example • This setvar example shows the value set to "on".
! U1 setvar "ip.snmp.enable" "on"
When the setvar value is set to "on", the getvar result is "on".
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 199

SGD Printer Settings 199
ip.tcp.enable
ip.tcp.enable
Description This printer setting refers to the TCP socket protocol.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage178.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the TCP status.
Format: ! U1 getvar "ip.tcp.enable"
setvar This command instructs the printer to turn the TCP on or off.
Format: ! U1 setvar "ip.tcp.enable" "value"
Values:
"off" = disables TCP protocol
"on" = enables TCP protocol
Default: "on"
Example • This setvar example shows the value set to "on".
! U1 setvar "ip.tcp.enable" "on"
When the setvar value is set to "on", the getvar result is "on".
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 200

200 SGD Printer Settings
ip.udp.enable
ip.udp.enable
Description This printer setting refers to the UDP socket protocol.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage178.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the UDP status.
Format: ! U1 getvar "ip.udp.enable"
setvar This command instructs the printer to turn UDP on or off.
Format: ! U1 setvar "ip.udp.enable" "value"
Values:
"off" = disables UDP protocol
"on" = enables UDP protocol
Default: "off"
Example • This setvar example shows the value set to "on".
! U1 setvar "ip.udp.enable" "on"
When the setvar value is set to "on", the getvar result is "on".
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 201

SGD Printer Settings 201
media.printmode
media.printmode
Description This printer setting determines the action the printer takes after a label or group
of labels has printed. This command is only supported on Zebra ZM400/ZM600™ printers.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage178.
• For details on the syntax and use of SGD commands, SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the the currently set
media print mode.
Format: ! U1 getvar "media.printmode"
setvar This command instructs the printer to change the media print mode.
Format: ! U1 setvar "media.printmode" "value"
Values:
tear off = "T"
peel off = "P"
rewind = "R"
applicator = "A"
cutter = "C"
delayed cutter = "D"
reserved = "L" *
reserved = "U" *
* These values are only supported on the Zebra ZM400/ZM600™ printer.
Example • This setvar example shows the value set to "T".
! U1 setvar "media.printmode" "T"
What the setvar value is set to is the getvar result. In this example, the getvar result is
"tear off".
For more details on how each setvar value relates to the getvar responses, see Table5,
Setvar / Getvar Relation onpage202.
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 202

202 SGD Printer Settings
media.printmode
Table 5 • Setvar / Getvar Relation
If the setvar is set to … Then the getvar response and front panel display is …
"T" TEAR OFF
"P" PEEL OFF
"R" REWIND
"A" APPLICATOR
"C" CUTTER
"D" DELAYED CUT
"L" RESERVED
"U" RESERVED
14245L-001 Rev. A EPL Programming Guide 5/30/07

---

## Pagina 203

SGD Printer Settings 203
odometer.headclean
odometer.headclean
Description This printer setting refers to the head clean odometer count. This counter tracks
how many inches and centimeters have passed through the printer since the head was last
cleaned. This command is only supported on Zebra ZM400/ZM600™ and S4M™ printers.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage178.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to retrieve the values for the head clean
counter.
Format: ! U1 getvar "odometer.headclean"
setvar This command instructs the printer to reset the head clean counter.
Format: ! U1 setvar "odometer.headclean" "value"
Values:
"0" = reset the head clean counter
Default: must be an accepted value or it is ignored
Example • This example shows how to get the odometer head clean, how to reset it, and how
to confirm the settings changed.
1. To see the current settings, type:
! U1 getvar "odometer.headclean"
Something similar to this is shown:
"1489 INCHES, 3784 CENTIMETERS"
2. To reset the these values to 0, type:
! U1 setvar "odometer.headclean" "0"
3. To confirm this settings were reset, type:
! U1 getvar "odometer.headclean"
If the resetting was successful, this is shown:
"0 INCHES, 0 CENTIMETERS"
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 204

204 SGD Printer Settings
odometer.headnew
odometer.headnew
Description This printer setting refers to the head replaced odometer count. This counter
tracks how many inches and centimeter passed through the printer since the head was last
replaced. This command is only supported on Zebra ZM400/ZM600™ and S4M™ printers.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage178.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to retrieve the values for the head new
counter.
Format: ! U1 getvar "odometer.headnew"
setvar This command instructs the printer to reset the head new counter.
Format: ! U1 setvar "odometer.headnew" "value"
Values:
"0" = resets the head new counter
Default: must be an accepted value or it is ignored
Example • This example shows how to get the odometer head new, how to reset it, and how
to confirm the settings changed:
1. To see the current settings, type:
! U1 getvar "odometer.headnew"
Something similar to this is shown:
"1489 INCHES, 3784 CENTIMETERS"
2. To reset the these values to 0, type:
! U1 setvar "odometer.headnew" "0"
3. To confirm this settings were reset, type:
! U1 getvar "odometer.headnew"
If the resetting was successful, this is shown:
"0 INCHES, 0 CENTIMETERS
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 205

SGD Printer Settings 205
odometer.label_dot_length
odometer.label_dot_length
Description This command returns the length of the last label printed or fed (in dots). This
command is only supported on Zebra ZM400/ZM600™ and S4M™ printers.
Type getvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage178.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the command for this format:
Commands Details
getvar This command returns the length of the last label printed or fed (in dots).
Format:
! U1 getvar "odometer.label_dot_length"
Example • This is an example of how to reset the length using the ^LL command and how to
use the getvar to confirm the change. For the ^LL command to work the printer must be in
continuous mode.
1. To change the odometer label dot length, type:
^XA
^LL500
^XZ
2. To get the current odometer label dot length, type:
! U1 getvar "odometer.label_dot_length"
Something similar to this is shown:
"500"
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 206

206 SGD Printer Settings
odometer.media_marker_count1
odometer.media_marker_count1
Description This printer setting refers to the value of the first (count1) user resettable
counter. The user resettable counters track how much media has passed through the printer in
both inches or centimeters. This command is only supported on Zebra ZM400/ZM600™ and
S4M™ printers.
Type getvar; setvar
Note • Here are some reference links for this command:
• For details on the command structure of SGD commands, see Command Structure
onpage178.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to return the current value of the first
(count1) user resettable counter in both inches and centimeters.
Format: ! U1 getvar "odometer.media_marker_count1"
setvar This command instructs the printer to reset the first user resettable counter.
Format:
! U1 setvar "odometer.media_marker_count1" "value"
Values:
"0" = reset the counter
Default: must be an accepted value or it is ignored
Example • This example shows how to get the first user resettable counter, how to reset it,
and how to confirm the settings have changed:
1. To see the current settings, type:
! U1 getvar "odometer.media_marker_count1"
Something similar to this is shown:
"8516 INCHES, 21632 CENTIMETERS"
2. To reset the these values to 0, type:
! U1 setvar "odometer.media_marker_count1" "0"
3. To confirm these settings were reset, type:
! U1 getvar "odometer.media_marker_count1"
If the resetting was successful, this is shown:
"0 INCHES, 0 CENTIMETERS"
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 207

SGD Printer Settings 207
odometer.media_marker_count2
odometer.media_marker_count2
Description This printer setting refers to the value of the second (count2) user resettable
counter. The user resettable counters track how much media has passed through the printer in
both inches or centimeters. This command is only supported on Zebra ZM400/ZM600™ and
S4M™ printers.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage178.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to return the current value of the
second (count2) user resettable counter in both inches and centimeters.
Format: ! U1 getvar "odometer.media_marker_count2"
setvar This command instructs the printer to reset the second user resettable
counter.
Format:
! U1 setvar "odometer.media_marker_count2" "value"
Values:
"0" = reset the counter
Default: must be an accepted value or it is ignored
Example • This example shows how to get the second user resettable counter, how to reset
it, and how to confirm the settings have changed:
1. To see the current settings, type:
! U1 getvar "odometer.media_marker_count2"
Something similar to this is shown:
"8516 INCHES, 21632 CENTIMETERS"
2. To reset the these values to 0, type:
! U1 setvar "odometer.media_marker_count2" "0"
3. To confirm these settings were reset, type:
! U1 getvar "odometer.media_marker_count2"
If the resetting was successful, this is shown:
"0 INCHES, 0 CENTIMETERS"
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 208

208 SGD Printer Settings
odometer.total_print_length
odometer.total_print_length
Description This command tracks the total length of media that printed over the life of the
printer. This command is only supported on Zebra ZM400/ZM600™ and S4M™ printers.
Type getvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage178.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the command for this format:
Commands Details
getvar This command returns the value of the total length of media that printed
over the life of the printer.
Format: ! U1 getvar "odometer.total_print_length"
Example • This example shows how to get the total length of media that printed over the life
of the printer.
1. To get the total length of media that has printed to date, type:
! U1 getvar "odometer.total_print_length"
Something similar to this is shown:
"8560 INCHES, 21744 CENTIMETERS"
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 209

5/30/07 EPL Programming Guide 14245L-001 Rev. A
5
SGD Wired Commands
This chapter provides a high-level overview of the wired Set/Get/Do (SGD) commands. The
commands in this chapter are only supported on Zebra ZM400/ZM600™ and S4M™ printers
running E53.15.xZ or later.
Important • These are important points to note when using EPL, ZPL, and SGD commands:
• SGD commands are case-sensitive.
• EPL, ZPL, and SGD commands should be sent to the printer as separate files.
• Certain settings can be controlled by EPL, ZPL, and SGD. Configuration changes made in
EPL or ZPL can affect configuration changes made in SGD.
• Changes made with one command type (EPL, ZPL, and SGD) will affect the data returned
to the host in response to EPL, ZPL, and getvar commands. The command type (EPL,
ZPL, or SGD) that was sent last determines the current setting.
• Some RF cards do not support all of the SGD commands.

|  |
| --- |
|  |


---

## Pagina 210

210 SGD Wired Commands
Overview
14245L-001 Rev. A EPL Programming Guide 5/30/07
Overview
This section describes how and why to use the Set/Get/Do (SGD) commands. It also
provides an example of a typical command structure.
Note • SGD commands must be terminated by a carriage return or a space and line feed.
SGD commands are commands that allow you to configure all printers with firmware versions
E53.15.x or later. The printer performs the specified function immediately after receiving the
command. The commands are:
(cid:129) setvar
(cid:129) getvar
(cid:129) do
setvar Command
Setvar commands:
• are used to configure printer settings to specific values by setting them in the printer
• must be terminated by a space character or a CR/ LF (0x0D, 0x0A)
Important • The setvar command and attributes must be specified in lower case.
getvar Command
Getvar commands:
• are used to get the current value of the printer settings
• must be terminated by a space character or CR/LF (0x0D, 0x0A)
The printer responds with the printer setting of “?” if:
• the printer setting does not exist (usually due to incorrect spelling of the printer setting)
• it has not been configured yet
Important • The printer settings and attributes must be specified in lower case.

---

## Pagina 211

SGD Wired Commands 211
Overview
5/30/07 EPL Programming Guide 14245L-001 Rev. A
do Command
Do commands:
• are used to instruct the printer to perform predefined actions
• must be terminated by a space character or a CR/LF (0x0D, 0x0A)
Some Do commands require additional settings which must be enclosed in double quotes.
Important • The values must be specified in lower case.

---

## Pagina 212

212 SGD Wired Commands
Command Structure
14245L-001 Rev. A EPL Programming Guide 5/30/07
Command Structure
It is important to understand the structure of the command and its components. A command
structure illustration is provided for each command in this guide.
Example • This is an example of a command structure illustration:
! U1 setvar "ip.addr" "value"
1 2 3
1 Command—always preceded with an exclamation point (!) and must be specified in
lower case. A space resides between the !and U1 and between U1 and the command
(setvar or getvar).
2 Attribute—always in double quotes and must be specified in lower case.
3 Chosen value—always in double quotes. Only applicable for setvar and do.
This command must be terminated by a space character or a CR/ LF (0x0D, 0x0A).
Note • Some RF cards do not support all of the SGD commands.
How to Send Multiple SGD Commands
For any getvar, setvar, or do command, if you issue the syntax without the "1" and use
the END command followed by a space, multiple SGD commands are sent simultaneously.
Example • This syntax shows how you can send multiple getvar commands:
1 ! U getvar "ip.telnet.enable"
getvar "ip.dhcp.enable"
2
getvar "ip.dhcp.cid_prefix"
3 END
1 The command portion of the string does not use the "1" after the "! U".
2 Commands issued after the first command do not require the "! U".
3 The string of commands is terminated by the word "END" with a space after the
word, and by a carriage return/ line feed.

|  |  |
| --- | --- |
|  |  |


|  |  |
| --- | --- |
|  |  |


---

## Pagina 213

SGD Wired Commands 213
external_wired.ip.addr
external_wired.ip.addr
Description This command allows you to get or set the external wired print servers’s IP
address. This command is only supported on Zebra ZM400/ZM600™ and S4M™ printers
running E53.15.xZ or later. This feature works with the ZebraNet 10/100 External Print
Server, firmware version 1.1.5.
Type getvar; setvar
Important • For a set IP address to take affect, the IP protocol must be set to permanent and
the print server must be reset.
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage212.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with its current external
wired print server IP address.
Format: ! U1 getvar "external_wired.ip.addr"
setvar This command instructs the printer to change its current external wired
print server IP address upon powering the printer on.
Format: ! U1 setvar "external_wired.ip.addr" "value"
Values: any valid IP address
Default: "0.0.0.0"
Note • The setvar value of this command can be affected by the
external_wired.ip.dhcp.enable command.
Example • This setvar example shows the value set to "10.14.4.235".
! U1 setvar "external_wired.ip.addr" "10.14.4.235"
What the setvar value is set to is the getvar result. In this example, the getvar result is
"10.14.4.235".
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 214

214 SGD Wired Commands
external_wired.ip.arp_interval
external_wired.ip.arp_interval
Description This print server setting allows you to specify the ARP (Address Resolution
Protocol) interval or the ARP cache time out for the external wired print server. This command
is only supported on Zebra ZM400/ZM600™ and S4M™ printers running E53.15.xZ or later.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage212.
• For details on the syntax and use of SGD commands, SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the ARP interval or the
ARP cache time out value for the external wired print server.
Format: ! U1 getvar
"external_wired.ip.arp_interval"
setvar This command instructs the printer to change the ARP interval or the ARP
cache time out for the external wired print server.
Format: ! U1 setvar "external_wired.ip.arp_interval"
"value"
Values: 0 - 30
Default: "0"
Example • This setvar example shows the value set to "0".
! U1 setvar "external_wired.ip.arp_interval" "0"
What the setvar value is set to is the getvar result. In this example, the getvar result is
"0".
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 215

SGD Wired Commands 215
external_wired.ip.default_addr.enable
external_wired.ip.default_addr.enable
Description This command allows you to default the external wired print server’s IP
address. This command is only supported on Zebra ZM400/ZM600™ and S4M™ printers
running E53.15.xZ or later.
Type getvar; setvar
Important • For a set IP address to take affect, the IP protocol must be set to permanent and
the print server must be reset.
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage212.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to show the status of the setting of
external wired print server’s default IP address feature.
Format:
! U1 getvar "external_wired.ip.default_addr.enable"
setvar This command tells the printer to use it’s default address, if no address is
provided through DHCP or BOOTP. If you do not assign an IP address
after 2 minutes, the 10/100 Internal PS defaults to IP address
192.168.254.254.
Format:
! U1 setvar "external_wired.ip.default_addr.enable" "value"
Values:
"on" = enabled
"off" = disabled
Default: "on"
Example • This setvar example shows the value set to "on".
! U1 setvar "external_wired.ip.default_addr.enable" "on"
What the setvar value is set to is the getvar result. In this example, the getvar result is
"on".
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 216

216 SGD Wired Commands
external_wired.ip.dhcp.cid_all
external_wired.ip.dhcp.cid_all
Description This printer setting defines the entire client identifier (DHCP option 61) if
DHCP is enabled on the external print server and
"external_wired.ip.dhcp.cid_type" is set to "0", or "2". The MAC address is
used if the type is set to "1". This command is only supported on Zebra ZM400/ZM600™
and S4M™ printers running E53.15.xZ or later. This feature works with the ZebraNet 10/100
External Print Server, firmware version 1.1.5.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage212.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the client identifier
prefix and suffix of the external wired print server.
Format:
! U1 getvar "external_wired.ip.dhcp.cid_all"
setvar This command instructs the printer to change the client identifier prefix
and suffix of the external wired print server. The prefix gets cleared and the
suffix contains the entire client identifier.
Format:
! U1 setvar "external_wired.ip.dhcp.cid_all" "value"
Values: A maximum length of 60 characters if the CID type is ASCII, or
120 characters if the CID type is hexadecimal.
Default Value: ""
Example • This setvar example shows the value set to "printer".
! U1 setvar "external_wired.ip.dhcp.cid_all" "printer"
What the setvar value is set to is the getvar result. In this example, the getvar result is
"printer".
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 217

SGD Wired Commands 217
external_wired.ip.dhcp.cid_enable
external_wired.ip.dhcp.cid_enable
Description This command determines if DHCP (option 61) on the external wired print
server is turned on or off. This command is only supported on Zebra ZM400/ZM600™ and
S4M™ printers running E53.15.xZ or later. This feature works with the ZebraNet 10/100
External Print Server, firmware version 1.1.5.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage212.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the status of the client
identifier of the external wired print server.
Format:
! U1 getvar "external_wired.ip.dhcp.cid_enable"
setvar This command instructs the printer to set the status of the client identifier
of the external wired print server.
Format:
! U1 setvar "external_wired.ip.dhcp.cid_enable" "value"
Values:
"off" = client identifier is turned off
"on" = client identifier is turned on
Default: "off"
Example • This setvar example shows the value set to "off".
! U1 setvar "external_wired.ip.dhcp.cid_enable" "off"
What the setvar value is set to is the getvar result. In this example, the getvar result is
"off".
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 218

218 SGD Wired Commands
external_wired.ip.dhcp.cid_prefix
external_wired.ip.dhcp.cid_prefix
Description This printer setting defines the prefix to be prepended to the DHCP client
identifier (option 61) when DHCP is enabled on the external wired print server and
"external_wired.ip.dhcp.cid_type" is set to "0" or "2". This command is only
supported on Zebra ZM400/ZM600™ and S4M™ printers running E53.15.xZ or later. This
feature works with the ZebraNet 10/100 External Print Server, firmware version 1.1.5.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage212.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the client identifier
prefix of the external wired print server.
Format:
! U1 getvar "external_wired.ip.dhcp.cid_prefix"
setvar This command instructs the printer to change the CID prefix of the
external wired print server.
Format:
! U1 setvar "external_wired.ip.dhcp.cid_prefix" "value"
Values: Any text string up to 10 characters if the CID type is ASCII, or 20
characters if the CID type is hexadecimal.
Default Value: ""
Example • This setvar example shows the value set to "PRT001".
! U1 setvar "external_wired.ip.dhcp.cid_prefix" "PRT001"
What the setvar value is set to is the getvar result. In this example, the getvar result is
"PRT001".
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 219

SGD Wired Commands 219
external_wired.ip.dhcp.cid_suffix
external_wired.ip.dhcp.cid_suffix
Description This printer setting defines the unique suffix to be used as the client identifier
(DHCP option 61) if DHCP is enabled repeated on the external wired print server and
external_wired.ip.dhcp.cid_type onpage220 is set to "0" or "2", not "1". This command is
only supported on Zebra ZM400/ZM600™ and S4M™ printers running E53.15.xZ or later.
This feature works with the ZebraNet 10/100 External Print Server, firmware version 1.1.5.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage212.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the client identifier
suffix on the external wired print server.
Format:
! U1 getvar "external_wired.ip.dhcp.cid_suffix"
setvar This command instructs the printer to change the client identifier suffix
value.
Format:
! U1 setvar "external_wired.ip.dhcp.cid_suffix"
"value"
Values: The maximum length of a value allowed is 60 ASCII characters
when the CID type is ASCII, or 120 hexadecimal values when the CID
type is hexadecimal.
Default Value: ""
Example • This setvar example shows setting the suffix to "printer".
! U1 setvar "external_wired.ip.dhcp.cid_suffix"
"printer"
What the setvar value is set to is the getvar result. In this example, the getvar result is
"printer".
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 220

220 SGD Wired Commands
external_wired.ip.dhcp.cid_type
external_wired.ip.dhcp.cid_type
Description This printer setting defines the type of client identifier (DHCP option 61) that
will be sent if DHCP is enabled on the external wired print server. A value of "1" means the
type of "Ethernet" and the printer’s MAC address will be used. A value of "0" or "2" means
the client identifier sent will be "external_wired.ip.dhcp.cid_prefix"
concatenated with "external_wired.ip.dhcp.cid_suffix". This command is
only supported on Zebra ZM400/ZM600™ and S4M™ printers running E53.15.xZ or later.
This feature works with the ZebraNet 10/100 External Print Server, firmware version 1.1.5.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage212.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the client identifier
type for the external wired print server.
Format:
! U1 getvar "external_wired.ip.dhcp.cid_type"
setvar This command instructs the printer to enable "synthetic" Client Identifier
for the external wired print server.
Format:
! U1 setvar "external_wired.ip.dhcp.cid_type" "value"
Values:
"0" = ASCII string
"1" = wired print server’s MAC address
"2" = HEX value
Default Value: "1"
Example • This setvar example shows the value set to "1".
! U1 setvar "external_wired.ip.dhcp.cid_type" "1"
What the setvar value is set to is the getvar result. In this example, the getvar result is
"1".
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 221

SGD Wired Commands 221
external_wired.ip.gateway
external_wired.ip.gateway
Description This command instructs the printer to change the external wired print server’s
gateway address. This command is only supported on Zebra ZM400/ZM600™ and S4M™
printers running E53.15.xZ or later.
Type getvar; setvar
Important • This setting refers to the gateway address. A set value is ignored if the IP
protocol is not set to permanent.
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage212.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the external wired
printer server’s gateway address.
Format: ! U1 getvar "external_wired.ip.gateway"
setvar This command instructs the printer to change the external wired printer
server’s gateway address.
Format:
! U1 setvar "external_wired.ip.gateway" "value"
Values: Any valid gateway address
Default: "0.0.0.0"
Example • This setvar example shows the value set to "10.3.5.1".
! U1 setvar "external_wired.ip.gateway" "10.3.5.1"
When the setvar value is set to "10.3.5.1", the getvar result is "10.3.5.1".
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 222

222 SGD Wired Commands
external_wired.ip.netmask
external_wired.ip.netmask
Description This setting refers to the external wired print server’s subnet mask address.
This value is ignored if the IP protocol is not set to permanent. This command is only
supported on Zebra ZM400/ZM600™ and S4M™ printers running E53.15.xZ or later.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage212.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the external wired print
server’s subnet mask.
Format: ! U1 getvar "external_wired.ip.netmask"
setvar This command instructs the printer to change the external wired print
servers’s subnet mask.
Format:
! U1 setvar "external_wired.ip.netmask" "value"
Values: Any valid subnet mask.
Default: "255.255.255.0"
Example • This setvar example shows the value set to "255.255.255.0".
! U1 setvar "external_wired.ip.netmask" "255.255.255.0"
When the setvar value is set to "255.255.255.0", the getvar result is
"255.255.255.0".
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 223

SGD Wired Commands 223
external_wired.ip.port
external_wired.ip.port
Description This printer setting refers to the external wired print server’s port number that
the TCP print service is listening on. Normal TCP communications from the host should be
directed to this port. This command is only supported on Zebra ZM400/ZM600™ and S4M™
printers running E53.15.xZ or later.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage212.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the external wired
printer server’s TCP/UDP port number.
Format: ! U1 getvar "external_wired.ip.port"
setvar This command instructs the printer to set the external wired print server’s
TCP/UDP port number.
Format: ! U1 setvar "external_wired.ip.port" "value"
Values: 1 - 65535 (excluding any ports currently used by other services,
such as 21, 23, 80, and 515).
Default: "9100"
Example • This setvar example shows the value set to "9100".
! U1 setvar "external_wired.ip.port" "9100"
When the setvar value is set to "9100", the getvar result is "9100".
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 224

224 SGD Wired Commands
external_wired.ip.protocol
external_wired.ip.protocol
Description This command configures the IP addressing method used by the external wired
print server. This command is only supported on Zebra ZM400/ZM600™ and S4M™ printers
running E53.15.xZ or later.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage212.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the command for this format:
Commands Details
getvar This command returns the IP addressing method used by the external print
server.
Format: ! U1 getvar "external_wired.ip.protocol"
setvar This command instructs the printer to configure the IP addressing method
used by the external wired print server.
Format:
! U1 setvar "external_wired.ip.protocol" "value"
Values:
"bootp" = uses the standard bootp addressing method to obtain an
IP address and configuration
"dhcp" = uses the standard dhcp addressing method to obtain an IP
address and configuration for a server specified period of time
"rarp" = uses the standard rarp addressing method to obtain an IP
address
"glean" = uses the IP address from a PING packet that is sent to its
hardware address (unicast address)
“permanent” = uses static values assigned through other
commands
"all" = tries all of the dynamic addressing methods, not permanent,
to obtain an IP address
Default: "all"
Example • In this example, the setvar result is the current programming language that the
printer is using.
! U1 setvar "external_wired.ip.protocol" "bootp"
What the setvar value is set to is the getvar result. In this example, the getvar result is
"bootp".
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 225

SGD Wired Commands 225
external_wired.ip.timeout.enable
external_wired.ip.timeout.enable
Description This network setting refers to enabling the connection timeout on the external
wired 10/100 print server. For this to take effect, the print server must be reset. This command
is only supported on Zebra ZM400/ZM600™ and S4M™ printers running E53.15.xZ or later.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage212.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to return whether the timeout checking
is enabled on the external wired print server.
Format:
! U1 getvar "external_wired.ip.timeout.enable"
setvar This command instructs the printer to enable or disable the timeout
checking on the external wired print server.
Format:
! U1 setvar "external_wired.ip.timeout.enable" "value"
Values:
"off" = turns off the connection checking
"on" = turns on the connection checking
Default: "on"
Example • This setvar example shows the value set to "on".
! U1 setvar "external_wired.ip.timeout.enable" "on"
When the setvar value is set to "on", the getvar result is "on".
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 226

226 SGD Wired Commands
external_wired.ip.timeout.value
external_wired.ip.timeout.value
Description This network setting refers to the number of seconds before the connection
times out for the external wired print server. This command is only supported on Zebra
ZM400/ZM600™ and S4M™ printers running E53.15.xZ or later.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage212.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the time of the external
wired print server, in seconds, before the connection times out.
Format:
! U1 getvar "external_wired.ip.timeout.value"
setvar This command instructs the printer to set the time of the external wired
print server, in seconds, before the connection times out.
Format:
! U1 setvar "external_wired.ip.timeout.value"
"value"
Values: "0" through "3600"
Default: "300"
Example • This setvar example shows the value set to "300".
! U1 setvar "external_wired.ip.timeout.value" "300"
When the setvar value is set to "300", the getvar result is "300".
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 227

SGD Wired Commands 227
external_wired.mac_addr
external_wired.mac_addr
Description This command retrieves the MAC address of the external wired print server.
This command is only supported on Zebra ZM400/ZM600™ and S4M™ printers running
E53.15.xZ or later.
Type getvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage212.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the command for this format:
Commands Details
getvar This command instructs the printer to respond with the MAC address of
the external wired print server.
Format: ! U1 getvar "external_wired.mac_addr"
Example • In this example, the getvar result is the MAC address of the external wired
print server.
! U1 getvar "external_wired.mac_addr"
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 228

228 SGD Wired Commands
internal_wired.ip.addr
internal_wired.ip.addr
Description This command allows you to get or set the internal wired print servers’s IP
address. This command is only supported on Zebra ZM400/ZM600™ and S4M™ printers
running E53.15.xZ or later. This command is only supported on ZM400/ZM600™ printers
with a ZebraNet™ 10/100 Internal Print Server.
Type getvar; setvar
Important • For a set IP address to take affect, the IP protocol must be set to permanent and
the print server must be reset.
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage212.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with its current internal
wired print server IP address.
Format: ! U1 getvar "internal_wired.ip.addr"
setvar This command instructs the printer to change its current internal wired
print server IP address upon powering the printer on.
Format: ! U1 setvar "internal_wired.ip.addr" "value"
Values: any valid IP address
Default: "0.0.0.0"
Note • The setvar value of this command can be affected by the
internal_wired.ip.dhcp.enable command.
Example • This setvar example shows the value set to "10.14.4.235".
! U1 setvar "internal_wired.ip.addr" "10.14.4.235"
What the setvar value is set to is the getvar result. In this example, the getvar result is
"10.14.4.235".
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 229

SGD Wired Commands 229
internal_wired.ip.arp_interval
internal_wired.ip.arp_interval
Description This print server setting allows you to specify the ARP (Address Resolution
Protocol) interval or the ARP cache time out for the internal wired print server. This command
is only supported on ZM400/ZM600™ printers with a ZebraNet™ 10/100 Internal Print
Server.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage212.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the ARP interval or the
ARP cache time out value for the internal wired print server.
Format:
! U1 getvar "internal_wired.ip.arp_interval"
setvar This command instructs the printer to change the ARP interval or the ARP
cache time out for the internal wired print server.
Format:
! U1 setvar "internal_wired.ip.arp_interval" "value"
Values: 0 - 30
Default: "0"
Example • This setvar example shows the value set to "0".
! U1 setvar "internal_wired.ip.arp_interval" "0"
What the setvar value is set to is the getvar result. In this example, the getvar result is
"0".
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 230

230 SGD Wired Commands
internal_wired.ip.default_addr.enable
internal_wired.ip.default_addr.enable
Description This command allows you to default the internal wired print server’s IP
address. This command is only supported on Zebra ZM400/ZM600™ and S4M™ printers
running E53.15.xZ or later. This command is only supported on ZM400/ZM600™ printers
with a ZebraNet™ 10/100 Internal Print Server.
Type getvar; setvar
Important • For a set IP address to take affect, the IP protocol must be set to permanent and
the print server must be reset.
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage212.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to show the status of the setting of
internal wired print server’s default IP address feature.
Format:
! U1 getvar "internal_wired.ip.default_addr.enable"
setvar This command tells the printer to use it’s default address, if no address is
provided through DHCP or BOOTP. If you do not assign an IP address
after 2 minutes, the 10/100 Internal PS defaults to IP address
192.168.254.254.
Format:
! U1 setvar "internal_wired.ip.default_addr.enable" "value"
Values:
"on" = enabled
"off" = disabled
Default: "on"
Example • This setvar example shows the value set to "on".
! U1 setvar "internal_wired.ip.default_addr.enable" "on"
What the setvar value is set to is the getvar result. In this example, the getvar result is
"on".
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 231

SGD Wired Commands 231
internal_wired.ip.dhcp.cid_all
internal_wired.ip.dhcp.cid_all
Description This printer setting defines the entire client identifier (DHCP option 61) if
DHCP is enabled on the internal print server and
"internal_wired.ip.dhcp.cid_type" is set to "0", or "2". The MAC address is
used if the type is set to "1". This command is only supported on Zebra ZM400/ZM600™
and S4M™ printers running E53.15.xZ or later. This command is only supported on
ZM400/ZM600™ printers with a ZebraNet™ 10/100 Internal Print Server.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage212.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the client identifier
prefix and suffix of the internal wired print server.
Format:
! U1 getvar "internal_wired.ip.dhcp.cid_all"
setvar This command instructs the printer to change the client identifier prefix
and suffix of the internal wired print server. The prefix gets cleared and the
suffix contains the entire client identifer.
Format:
! U1 setvar "internal_wired.ip.dhcp.cid_all"
"value"
Values: A maximum length of 60 characters if the CID type is ASCII, or
120 characters if the CID type is hexadecimal.
Default Value: ""
Example • This setvar example shows the value set to "printer".
! U1 setvar "internal_wired.ip.dhcp.cid_all" "printer"
What the setvar value is set to is the getvar result. In this example, the getvar result is
"printer".
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 232

232 SGD Wired Commands
internal_wired.ip.dhcp.cid_enable
internal_wired.ip.dhcp.cid_enable
Description This command determines if DHCP (option 61) is turned on or off of the
internal wired print server. This command is only supported on Zebra ZM400/ZM600™ and
S4M™ printers running E53.15.xZ or later. This command is only supported on
ZM400/ZM600™ printers with a ZebraNet™ 10/100 Internal Print Server.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage212.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the status of the client
identifier of the internal wired print server.
Format:
! U1 getvar "internal_wired.ip.dhcp.cid_enable"
setvar This command instructs the printer to set the status of the client identifier
of the internal wired print server.
Format:
! U1 setvar "internal_wired.ip.dhcp.cid_enable" "value"
Values:
"off" = client identifier is turned off
"on" = client identifier is turned on
Default: "off"
Example • This setvar example shows the value set to "off".
! U1 setvar "internal_wired.ip.dhcp.cid_enable" "off"
What the setvar value is set to is the getvar result. In this example, the getvar result is
"off".
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 233

SGD Wired Commands 233
internal_wired.ip.dhcp.cid_prefix
internal_wired.ip.dhcp.cid_prefix
Description This printer setting defines the prefix to be prepended to the DHCP client
identifier (option 61) when DHCP is enabled on the internal wired print server and
"internal_wired.ip.dhcp.cid_type" is set to "0" or "2". This command is only
supported on Zebra ZM400/ZM600™ and S4M™ printers running E53.15.xZ or later. This
command is only supported on ZM400/ZM600™ printers with a ZebraNet™ 10/100 Internal
Print Server.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage212.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the client identifier
prefix of the internal wired print server.
Format:
! U1 getvar "internal_wired.ip.dhcp.cid_prefix"
setvar This command instructs the printer to change the CID prefix of the internal
wired print server.
Format:
! U1 setvar "internal_wired.ip.dhcp.cid_prefix" "value"
Values: Any text string up to 10 characters if the CID type is ASCII, or 20
characters if the CID type is hexadecimal.
Default Value: ""
Example • This setvar example shows the value set to "PRT001".
! U1 setvar "internal_wired.ip.dhcp.cid_prefix" "PRT001"
What the setvar value is set to is the getvar result. In this example, the getvar result is
"PRT001".
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 234

234 SGD Wired Commands
internal_wired.ip.dhcp.cid_suffix
internal_wired.ip.dhcp.cid_suffix
Description This printer setting defines the unique suffix to be used as the client identifier
(DHCP option 61) if DHCP is enabled on the internal wired 10/100 print server and
"internal_wired.ip.dhcp.cid_type" is set to "0" or "2", not "1". This
command is only supported on Zebra ZM400/ZM600™ and S4M™ printers running
E53.15.xZ or later. This command is only supported on ZM400/ZM600™ printers with a
ZebraNet™ 10/100 Internal Print Server.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage212.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the client identifier
suffix of the internal wired 10/100 print server.
Format:
! U1 getvar "internal_wired.ip.dhcp.cid_suffix"
setvar This command instructs the printer to change the client identifier suffix
value of the internal wired 10/100 print server.
Format:
! U1 setvar "internal_wired.ip.dhcp.cid_suffix"
"value"
Values: The maximum length of a value allowed is 60 ASCII characters
when the CID type is ASCII, or 120 hexadecimal values when the CID
type is hexadecimal.
Default Value: ""
Example • This setvar example shows the value set to "printer".
! U1 setvar "internal_wired.ip.dhcp.cid_suffix" "printer"
What the setvar value is set to is the getvar result. In this example, the getvar result is
"printer".
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 235

SGD Wired Commands 235
internal_wired.ip.dhcp.cid_type
internal_wired.ip.dhcp.cid_type
Description This printer setting defines the type of client identifier (DHCP option 61) that
will be sent if DHCP is enabled on the internal wired print server. A value of "1" means the
type of "Ethernet" and the printer’s MAC address will be used.A value of "0" or "2" means
the client identifier sent will be "internal_wired.ip.dhcp.cid_prefix"
concatenated with "internal_wired.ip.dhcp.cid_suffix". This command is
only supported on Zebra ZM400/ZM600™ and S4M™ printers running E53.15.xZ or later.
This command is only supported on ZM400/ZM600™ printers with a ZebraNet™ 10/100
Internal Print Server.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage212.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the client identifier
type for the internal wired print server.
Format:
! U1 getvar "internal_wired.ip.dhcp.cid_type"
setvar This command instructs the printer to enable "synthetic" Client Identifier
for the internal wired print server.
Format:
! U1 setvar "internal_wired.ip.dhcp.cid_type" "value"
Values:
"0" = ASCII string
"1" = wired print server’s MAC address
"2" = HEX value
Default Value: "1"
Example • This setvar example shows the value set to "1".
! U1 setvar "internal_wired.ip.dhcp.cid_type" "1"
What the setvar value is set to is the getvar result. In this example, the getvar result is
"1".
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 236

236 SGD Wired Commands
internal_wired.ip.gateway
internal_wired.ip.gateway
Description This command instructs the printer to change the internal wired print servers
gateway address. This command is only supported on Zebra ZM400/ZM600™ and S4M™
printers running E53.15.xZ or later. This command is only supported on ZM400/ZM600™
printers with a ZebraNet™ 10/100 Internal Print Server.
Type getvar; setvar
Important • This setting refers to the gateway address. A set value is ignored if the IP
protocol is not set to permanent.
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage212.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the internal wired
printer servers gateway address.
Format: ! U1 getvar "internal_wired.ip.gateway"
setvar This command instructs the printer to change the internal wired printer
servers gateway address.
Format:
! U1 setvar "internal_wired.ip.gateway" "value"
Values: Any valid gateway address
Default: "0.0.0.0"
Example • This setvar example shows the value set to "10.3.5.1".
! U1 setvar "internal_wired.ip.gateway" "10.3.5.1"
When the setvar value is set to "10.3.5.1", the getvar result is "10.3.5.1".
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 237

SGD Wired Commands 237
internal_wired.ip.netmask
internal_wired.ip.netmask
Description This setting refers to the internal wired print server’s subnet mask address. This
value is ignored if the IP protocol is not set to permanent. This command is only supported on
Zebra ZM400/ZM600™ and S4M™ printers running E53.15.xZ or later. This command is
only supported on ZM400/ZM600™ printers with a ZebraNet™ 10/100 Internal Print Server.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage212.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with internal wired print
servers subnet mask.
Format: ! U1 getvar "internal_wired.ip.netmask"
setvar This command instructs the printer to change the internal wired print
servers subnet mask.
Format:
! U1 setvar "internal_wired.ip.netmask" "value"
Values: Any valid subnet mask.
Default: "255.255.255.0"
Example • This setvar example shows the value set to "255.255.255.0".
! U1 setvar "internal_wired.ip.netmask" "255.255.255.0"
When the setvar value is set to "255.255.255.0", the getvar result is
"255.255.255.0".
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 238

238 SGD Wired Commands
internal_wired.ip.port
internal_wired.ip.port
Description This printer setting refers to the internal wired print servers port number that
the TCP print service is listening on. Normal TCP communications from the host should be
directed to this port. This command is only supported on Zebra ZM400/ZM600™ and S4M™
printers running E53.15.xZ or later. This command is only supported on ZM400/ZM600™
printers with a ZebraNet™ 10/100 Internal Print Server.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage212.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the internal wired
printer servers TCP/UDP port number.
Format: ! U1 getvar "internal_wired.ip.port"
setvar This command instructs the printer to set the internal wired print servers
TCP/UDP port number.
Format: ! U1 setvar "internal_wired.ip.port" "value"
Values: 1 - 65535 (excluding any ports currently used by other services,
such as 21, 23, 80, and 515).
Default: "9100"
Example • This setvar example shows the value set to "9100".
! U1 setvar "internal_wired.ip.port" "9100"
When the setvar value is set to "9100", the getvar result is "9100".
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 239

SGD Wired Commands 239
internal_wired.ip.protocol
internal_wired.ip.protocol
Description This command configures the IP addressing method used by the internal wired
print server. This command is only supported on Zebra ZM400/ZM600™ and S4M™ printers
running E53.15.xZ or later. This command is only supported on ZM400/ZM600™ printers
with a ZebraNet™ 10/100 Internal Print Server.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage212.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the command for this format:
Commands Details
getvar This command returns the IP addressing method used by the internal wired
print server.
Format: ! U1 getvar "internal_wired.ip.protocol"
setvar This command instructs the printer to configure the IP addressing method
used by the internal wired print server.
Format:
! U1 setvar "internal_wired.ip.protocol" "value"
Values:
"bootp" = uses the standard bootp addressing method to obtain an
IP address and configuration
"dhcp" = uses the standard dhcp addressing method to obtain an IP
address and configuration for a server specified period of time
"rarp" = uses the standard rarp addressing method to obtain an IP
address
"glean" = uses the IP address from a PING packet that is sent to its
hardware address (unicast address)
“permanent” = uses static values assigned through other
commands
"all" = tries all of the dynamic addressing methods, not permanent,
to obtain an IP address
Default: "all"
Example • In this example, the setvar result is the current programming language that the
printer is using.
! U1 setvar "internal_wired.ip.protocol" "bootp"
What the setvar value is set to is the getvar result. In this example, the getvar result is
"bootp".
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 240

240 SGD Wired Commands
internal_wired.ip.timeout.enable
internal_wired.ip.timeout.enable
Description This network setting refers to enabling the connection timeout on the internal
wired print server. For this to take effect, the print server must be reset. This command is only
supported on Zebra ZM400/ZM600™ and S4M™ printers running E53.15.xZ or later. This
command is only supported on ZM400/ZM600™ printers with a ZebraNet™ 10/100 Internal
Print Server.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage212.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to return whether the timeout checking
is enabled on the internal wired print server.
Format:
! U1 getvar "internal_wired.ip.timeout.enable"
setvar This command instructs the printer to enable or disable the timeout
checking on the internal wired print server.
Format:
! U1 setvar "internal_wired.ip.timeout.enable" "value"
Values:
"off" = turns off the connection checking
"on" = turns on the connection checking
Default: "on"
Example • This setvar example shows the value set to "on".
! U1 setvar "internal_wired.ip.timeout.enable" "on"
When the setvar value is set to "on", the getvar result is "on".
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 241

SGD Wired Commands 241
internal_wired.ip.timeout.value
internal_wired.ip.timeout.value
Description This network setting refers to the number of seconds before the connection
times out for the internal wired print server. For this to take effect, the print server must be
reset. This command is only supported on Zebra ZM400/ZM600™ and S4M™ printers
running E53.15.xZ or later. This command is only supported on ZM400/ZM600™ printers
with a ZebraNet™ 10/100 Internal Print Server.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage212.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the time of the internal
wired print server, in seconds, before the connection times out.
Format:
! U1 getvar "internal_wired.ip.timeout.value"
setvar This command instructs the printer to set the time of the internal wired
print server, in seconds, before the connection times out.
Format:
! U1 setvar "internal_wired.ip.timeout.value"
"value"
Values: "0" through "3600"
Default: "300"
Example • This setvar example shows the value set to "300".
! U1 setvar "internal_wired.ip.timeout.value" "300"
When the setvar value is set to "300", the getvar result is "300".
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 242

242 SGD Wired Commands
internal_wired.mac_addr
internal_wired.mac_addr
Description This command retrieves the MAC address of the internal wired print server.
This command is only supported on Zebra ZM400/ZM600™ and S4M™ printers running
E53.15.xZ or later. This command is only supported on ZM400/ZM600™ printers with a
ZebraNet™ 10/100 Internal Print Server.
Type getvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage212.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the command for this format:
Commands Details
getvar This command instructs the printer to respond with the MAC address of
the internal wired print server.
Format: ! U1 getvar "internal_wired.mac_addr"
Example • In this example, the getvar result is the MAC address of the internal wired
print server.
! U1 getvar "internal_wired.mac_addr"
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 243

5/30/07 EPL Programming Guide 14245L-001 Rev. A
6
SGD Wireless Commands
This chapter provides a high-level overview of the wireless Set/Get/Do (SGD) commands
and details on each SGD command.
SGD commands are available in printers with firmware version E53.15.x or later.
Note • The commands listed in this chapter are for use with the Wireless Print Server and
Wireless Plus Print Server, when used with firmware version E53.15.x or later.
Important • These are important points to note when using EPL, ZPL, and SGD commands:
• SGD commands are case-sensitive.
• EPL, ZPL, and SGD commands should be sent to the printer as separate files.
• Certain settings can be controlled by EPL, ZPL, and SGD. Configuration changes made in
EPL or ZPL can affect configuration changes made in SGD.
• Changes made with one command type (EPL, ZPL, and SGD) will affect the data returned
to the host in response to EPL, ZPL, and getvar commands. The command type (EPL,
ZPL, or SGD) that was sent last determines the current setting.
• Some RF cards do not support all of the SGD commands.

|  |
| --- |
|  |


---

## Pagina 244

244 SGD Wireless Commands
Overview
14245L-001 Rev. A EPL Programming Guide 5/30/07
Overview
This section describes how and why to use the Set/Get/Do (SGD) commands. It also
provides an example of a typical command structure.
Note • SGD commands must be terminated by a carriage return or a space and line feed.
SGD commands are commands that allow you to configure all printers with firmware versions
E53.15.x or later. The printer performs the specified function immediately after receiving the
command. The commands are:
(cid:129) setvar
(cid:129) getvar
(cid:129) do
setvar Command
Setvar commands:
• are used to configure printer settings to specific values by setting them in the printer
• must be terminated by a space character or a CR/ LF (0x0D, 0x0A)
Important • The setvar command and attributes must be specified in lower case.
getvar Command
Getvar commands:
• are used to get the current value of the printer settings
• must be terminated by a space character or CR/LF (0x0D, 0x0A)
The printer responds with the printer setting of “?” if:
• the printer setting does not exist (usually due to incorrect spelling of the printer setting)
• it has not been configured yet
Important • The printer settings and attributes must be specified in lower case.

---

## Pagina 245

SGD Wireless Commands 245
Overview
5/30/07 EPL Programming Guide 14245L-001 Rev. A
do Command
Do commands:
• are used to instruct the printer to perform predefined actions
• must be terminated by a space character or a CR/LF (0x0D, 0x0A)
Some Do commands require additional settings which must be enclosed in double quotes.
Important • The values must be specified in lower case.

---

## Pagina 246

246 SGD Wireless Commands
Command Structure
14245L-001 Rev. A EPL Programming Guide 5/30/07
Command Structure
It is important to understand the structure of the command and its components. A command
structure illustration is provided for each command in this guide.
Example • This is an example of a command structure illustration:
! U1 setvar "ip.addr" "value"
1 2 3
1 Command—always preceded with an exclamation point (!) and must be specified in
lower case. A space resides between the !and U1 and between U1 and the command
(setvar or getvar).
2 Attribute—always in double quotes and must be specified in lower case.
3 Chosen value—always in double quotes. Only applicable for setvar and do.
This command must be terminated by a space character or a CR/ LF (0x0D, 0x0A).
Note • Some RF cards do not support all of the SGD commands.
How to Send Multiple SGD Commands
For any getvar, setvar, or do command, if you issue the syntax without the "1" and use
the END command followed by a space, multiple SGD commands are sent simultaneously.
Example • This syntax shows how you can send multiple getvar commands:
1 ! U getvar "ip.telnet.enable"
getvar "ip.dhcp.enable"
2
getvar "ip.dhcp.cid_prefix"
3 END
1 The command portion of the string does not use the "1" after the "! U".
2 Commands issued after the first command do not require the "! U".
3 The string of commands is terminated by the word "END" with a space after the
word, and by a carriage return/ line feed.

|  |  |
| --- | --- |
|  |  |


|  |  |
| --- | --- |
|  |  |


---

## Pagina 247

SGD Wireless Commands 247
card.inserted
card.inserted
Description This command indicates whether the wireless radio card is or is not inserted.
Type getvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the command for this format:
Commands Details
getvar This command instructs the printer to respond with the wireless radio card
status. It’s inserted or it’s not inserted.
Format: ! U1 getvar "card.inserted"
Example • In this example, the getvar result is "Inserted".
! U1 getvar "card.inserted"
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 248

248 SGD Wireless Commands
card.mac_addr
card.mac_addr
Description This command retrieves the MAC address of the wireless radio card.
Type getvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the command for this format:
Commands Details
getvar This command instructs the printer to respond with the MAC address.
Format: ! U1 getvar "card.mac_addr"
Example • In this example, the getvar result is the MAC address for the wireless radio
card.
! U1 getvar "card.mac_addr"
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 249

SGD Wireless Commands 249
ip.addr
ip.addr
Description This command allows you to get or set the printer’s IP address.
Type getvar; setvar
Important • For a set IP address to take affect, the IP protocol must be set to permanent and
the print server must be reset.
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with its current IP address.
Format: ! U1 getvar "ip.addr"
setvar This command instructs the printer to change its current IP address upon
powering the printer on.
Format: ! U1 setvar "ip.addr" "value"
Values: any valid IP address
Default: "0.0.0.0"
Note • The setvar value of this command can be affected by the ip.dhcp.enable
command.
Example • This setvar example shows the value set to "10.14.4.235".
! U1 setvar "ip.addr" "10.14.4.235"
What the setvar value is set to is the getvar result. In this example, the getvar result is
"10.14.4.235".
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 250

250 SGD Wireless Commands
ip.arp_interval
ip.arp_interval
Description This printer setting allows you to specify the ARP (Address Resolution
Protocol) interval or the ARP cache time out.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the ARP interval or the
ARP cache time out value in seconds.
Format: ! U1 getvar "ip.arp_interval"
setvar This command instructs the printer to change the ARP interval or the ARP
cache time out.
Format: ! U1 setvar "ip.arp_interval" "value"
Values: 0 - 30
Default: "0"
Example • This setvar example shows the value set to "0".
! U1 setvar "ip.arp_interval" "0"
What the setvar value is set to is the getvar result. In this example, the getvar result is
"0".
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 251

SGD Wireless Commands 251
ip.bootp.enable
ip.bootp.enable
Description This printer setting turns BOOTP on or off. BOOTP is a method for acquiring
an IP address, netmask, and gateway automatically on printer power-up. It requires a BOOTP
server on the local network.
Type getvar; setvar
Note • If you are using static IP addressing, the IP protocol must be set to permanent.
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the current BOOTP
setting.
Format: ! U1 getvar "ip.bootp.enable"
setvar This command instructs the printer to turn BOOTP on or off.
Format: ! U1 setvar "ip.bootp.enable" "value"
Values:
"off" = printer does not use BOOTP to get the IP address
"on" = printer uses BOOTP to get the IP address
Default: "on"
Example • This setvar example shows the value set to "on".
! U1 setvar "ip.bootp.enable" "on"
When the setvar value is set to "on", the getvar result is "on".
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 252

252 SGD Wireless Commands
ip.dhcp.cid_all
ip.dhcp.cid_all
Description This printer setting defines the entire client identifier (DHCP option 61) if the
DHCP is enabled and "ip.dhcp.cid_type" is set to "0", or "2". The MAC address is
used if the type is set to "1".
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the client identifier
prefix and suffix.
Format: ! U1 getvar "ip.dhcp.cid_all"
setvar This command instructs the printer to change the CID prefix and suffix.
Format: ! U1 setvar "ip.dhcp.cid_all" "value"
Values: A maximum length of 60 characters if the CID type is ASCII, or
120 characters if the CID type is hexadecimal.
Default Value: ""
Example • This setvar example shows the value set to "printer".
! U1 setvar "ip.dhcp.cid_all" "printer"
What the setvar value is set to is the getvar result. In this example, the getvar result is
"printer".
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 253

SGD Wireless Commands 253
ip.dhcp.cid_enable
ip.dhcp.cid_enable
Description This command determines if DHCP (option 61) is turned on or off.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the status of the client
identifier.
Format: ! U1 getvar "ip.dhcp.cid_enable"
setvar This command instructs the printer to set the status of the client identifier.
Format: ! U1 setvar "ip.dhcp.cid_enable" "value"
Values:
"off" = client identifier is turned off
"on" = client identifier is turned on
Default: "off"
Example • This setvar example shows the value set to "off".
! U1 setvar "ip.dhcp.cid_enable" "off"
What the setvar value is set to is the getvar result. In this example, the getvar result is
"off".
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 254

254 SGD Wireless Commands
ip.dhcp.cid_prefix
ip.dhcp.cid_prefix
Description This printer setting defines the prefix to be prepended to the DHCP client
identifier (option 61) when DHCP is enabled and "ip.dhcp.cid_type" is set to "0"or
"2".
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the client identifier
prefix.
Format: ! U1 getvar "ip.dhcp.cid_prefix"
setvar This command instructs the printer to change the CID prefix.
Format: ! U1 setvar "ip.dhcp.cid_prefix" "value"
Values: Any text string up to 10 characters if the CID type is ASCII, or
20characters if the CID type is hexadecimal.
Default Value: ""
Example • This setvar example shows the value set to "PRT001".
! U1 setvar "ip.dhcp.cid_prefix" "PRT001"
What the setvar value is set to is the getvar result. In this example, the getvar result is
"PRT001".
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 255

SGD Wireless Commands 255
ip.dhcp.cid_suffix
ip.dhcp.cid_suffix
Description This printer setting defines the unique suffix to be used as the client identifier
(DHCP option 61) if DHCP is enabled and "ip.dhcp.cid_type" is set to "0" or "2".
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the client identifier
suffix.
Format: ! U1 getvar "ip.dhcp.cid_suffix"
setvar This command instructs the printer to change the CID value.
Format: ! U1 setvar "ip.dhcp.cid_suffix" "value"
Values: The maximum length of a value allowed is 60 ASCII characters
when the CID type is ASCII, or 120 hexadecimal values when the CID
type is hexadecimal.
Default Value: ""
Example • This setvar example shows the value set to "printer".
! U1 setvar "ip.dhcp.cid_suffix" "printer"
What the setvar value is set to is the getvar result. In this example, the getvar result is
"printer".
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 256

256 SGD Wireless Commands
ip.dhcp.cid_type
ip.dhcp.cid_type
Description This printer setting defines the type of client identifier (DHCP option 61) that
will be sent if DHCP is enabled. A value of "1" means the type of "Ethernet" and the printer’s
MAC address will be used. A value of "0" or "2" means the client identifier sent will be
"ip.dhcp.cid_prefix" concatenated with "ip.dhcp.cid_suffix".
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the client identifier
type.
Format: ! U1 getvar "ip.dhcp.cid_type"
setvar This command instructs the printer to enable "synthetic" Client Identifier.
Format: ! U1 setvar "ip.dhcp.cid_type" "value"
Values:
"0" = ASCII string
"1" = wireless radio card’s MAC address
"2" = HEX value
Default Value: "1"
Example • This setvar example shows the value set to "1".
! U1 setvar "ip.dhcp.cid_type" "1"
What the setvar value is set to is the getvar result. In this example, the getvar result is
"1".
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 257

SGD Wireless Commands 257
ip.dhcp.enable
ip.dhcp.enable
Definition This printer setting turns DHCP on or off. DHCP is a method for acquiring an IP
address, netmask, and gateway automatically on printer power-up. It requires a DHCP server
on the local network.
Type getvar; setvar
Note • If you are using static IP addressing, the IP protocol must be set to permanent.
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the DHCP status.
Format: ! U1 getvar "ip.dhcp.enable"
setvar This command instructs the printer to turn DHCP on or off.
Format: ! U1 setvar "ip.dhcp.enable" "value"
Values:
"off" = printer does not use DHCP to get the IP address
"on" = printer uses DHCP to get the IP address
Default: "on"
Example • This setvar example shows the value set to "on".
! U1 setvar "ip.dhcp.enable" "on"
When the setvar value is set to "on", the getvar result is "on".
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 258

258 SGD Wireless Commands
ip.gateway
ip.gateway
Description This command instructs the printer to change the gateway address.
Type getvar; setvar
Note • This setting refers to the gateway address. A set value is ignored if the IP protocol is
not set to permanent.
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the gateway address.
Format: ! U1 getvar "ip.gateway"
setvar This command instructs the printer to change the gateway address.
Format: ! U1 setvar "ip.gateway" "value"
Values: Any valid gateway address
Default: "0.0.0.0"
Example • This setvar example shows the value set to "10.3.5.1".
! U1 setvar "ip.gateway" "10.3.5.1"
When the setvar value is set to "10.3.5.1", the getvar result is "10.3.5.1".
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 259

SGD Wireless Commands 259
ip.netmask
ip.netmask
Description This setting refers to the subnet mask address. This value is ignored if the IP
protocol is not set to permanent.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with subnet mask.
Format: ! U1 getvar "ip.netmask"
setvar This command instructs the printer to change the subnet mask.
Format: ! U1 setvar "ip.netmask" "value"
Values: Any valid subnet mask.
Default: "255.255.255.0"
Example • This setvar example shows the value set to "255.255.255.0".
! U1 setvar "ip.netmask" "255.255.255.0"
When the setvar value is set to "255.255.255.0", the getvar result is
"255.255.255.0".
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 260

260 SGD Wireless Commands
ip.port
ip.port
Description This printer setting refers to the port number that the TCP print service is
listening on. Normal TCP communications from the host should be directed to this port.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the TCP/UDP port
number.
Format: ! U1 getvar "ip.port"
setvar This command instructs the printer to set the TCP/UDP port number.
Format: ! U1 setvar "ip.port" "value"
Values: 1 - 65535 (excluding any ports currently used by other services,
such as 21, 23, 80, and 515).
Default: "9100"
Example • This setvar example shows the value set to "9100".
! U1 setvar "ip.port" "9100"
When the setvar value is set to "9100", the getvar result is "9100".
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 261

SGD Wireless Commands 261
wlan.adhocautomode
wlan.adhocautomode
Description This printer setting refers to enabling or disabling the adhoc auto mode.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the adhoc auto mode
status.
Format: ! U1 getvar "wlan.adhocautomode"
setvar This command instructs the printer to set the adhoc auto mode.
Format: ! U1 setvar "wlan.adhocautomode" "value"
Values:
"on" = adhoc auto mode enabled
"off" = adhoc auto mode disabled
Default: "off"
Example • This setvar example shows the value set to "on".
! U1 setvar "wlan.adhocautomode" "on"
When the setvar value is set to "on", the getvar result is "on".
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 262

262 SGD Wireless Commands
wlan.adhocchannel
wlan.adhocchannel
Description This printer setting refers to specifying the wireless channel for adhoc channel.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the wireless channel
for adhoc channel mode.
Format: ! U1 getvar "wlan.adhocchannel"
setvar This command instructs the printer to set the wireless channel for adhoc
channel mode.
Format: ! U1 setvar "wlan.adhocchannel" "value"
Values: Decimal value between 1 and 16 inclusive
Default: "1"
Example • This setvar example shows the value set to "1".
! U1 setvar "wlan.adhocchannel" "1"
When the setvar value is set to "1", the getvar result is "1".
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 263

SGD Wireless Commands 263
wlan.associated
wlan.associated
Description This command refers to if the printer is or is not associated with an access point
(AP).
Type getvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with yes or no, which
identifies if it is associated with the AP.
Format: ! U1 getvar "wlan.associated"
Example • In this example, the getvar result is "yes".
! U1 getvar "wlan.associated"
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 264

264 SGD Wireless Commands
wlan.channel_mask
wlan.channel_mask
Description This printer setting refers to specifying the wireless channel masks to enable
and disable various channels.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the wireless channel
mask value.
Format: ! U1 getvar "wlan.channel_mask"
setvar This command instructs the printer to set the wireless channel mask value.
Format: ! U1 setvar "wlan.channel_mask" "value"
Values: 4 Hexadecimal digits preceded by "0x" (0x0000 to 0xFFFF). For
commonly used channel masks, see Table6 onpage264.
Default: "0x7FF"
Table 6 • Channel Mask Settings
Region Channel Mask
United States, Canada, Latin America 0x7FF
Europe, Middle East, Africa, other 0x1FFF
Japan 0x3FFF
Example • This setvar example shows the value set to "0x7FF".
! U1 setvar "wlan.channel_mask" "0x7FF"
When the setvar value is set to "0x7FF", the getvar result is "0x7FF".
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 265

SGD Wireless Commands 265
wlan.essid
wlan.essid
Description This printer setting refers to the printer’s stored ESSID. Setting the ESSID to ""
will set the printer in a "broadcast" mode.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the stored ESSID
value.
Format: ! U1 getvar "wlan.essid"
setvar This command instructs the printer to change the ESSID.
Format: ! U1 setvar "wlan.essid" "value"
Values: 32 character alphanumeric string
Default: "125"
Example • This setvar example shows the value set to "125".
! U1 setvar "wlan.essid" "125"
When the setvar value is set to "125", the getvar result is "125".
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 266

266 SGD Wireless Commands
wlan.firmware_version
wlan.firmware_version
Description This command refers to the firmware version of the wireless radio card.
Type getvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the current version of
the wireless radio card firmware.
Format: ! U1 getvar "wlan.firmware_version"
Example • In this example, the getvar result is the version of Symbol 4137 card (for
example, "F3.91-69").
! U1 getvar "wlan.firmware_version"
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 267

SGD Wireless Commands 267
wlan.ip.addr
wlan.ip.addr
Description This command allows you to get or set the wireless print servers’s IP address.
This command is supported only on Zebra ZM400/ZM600™ and S4M™ printers.
Type getvar; setvar
Important • For a set IP address to take affect, the IP protocol must be set to permanent and
the print server must be reset.
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with its current wireless
print server IP address.
Format: ! U1 getvar "wlan.ip.addr"
setvar This command instructs the printer to change its current wireless print
server IP address upon powering the printer on.
Format: ! U1 setvar "wlan.ip.addr" "value"
Values: any valid IP address
Default: "0.0.0.0"
Note • The setvar value of this command can be affected by the
wlan.ip.dhcp.enable command.
Example • This setvar example shows the value set to "10.14.4.235".
! U1 setvar "wlan.ip.addr" "10.14.4.235"
What the setvar value is set to is the getvar result. In this example, the getvar result is
"10.14.4.235".
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 268

268 SGD Wireless Commands
wlan.ip.arp_interval
wlan.ip.arp_interval
Description This print server setting allows you to specify the ARP (Address Resolution
Protocol) interval or the ARP cache time out for the wireless print server. This command is
supported only on Zebra ZM400/ZM600™ and S4M™ printers.
Type getvar; setvar
Important • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the ARP interval or the
ARP cache time out value (in seconds) for the wireless print server.
Format: ! U1 getvar "wlan.ip.arp_interval"
setvar This command instructs the printer to change the ARP interval or the ARP
cache time out for the wireless print server.
Format: ! U1 setvar "wlan.ip.arp_interval" "value"
Values: 0 - 30
Default: "0"
Example • This setvar example shows the value set to "0".
! U1 setvar "wlan.ip.arp_interval" "0"
What the setvar value is set to is the getvar result. In this example, the getvar result is
"0".
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 269

SGD Wireless Commands 269
wlan.ip.default_addr.enable
wlan.ip.default_addr.enable
Description This command allows you to default the wireless print server’s IP address. This
command is only supported on Zebra ZM400/ZM600™ and S4M™ printers.
Type getvar; setvar
Important • For a set IP address to take affect, the IP protocol must be set to permanent and
the print server must be reset.
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to show the status of the setting of the
wireless print server’s default IP address feature.
Format:
! U1 getvar "wlan.ip.default_addr.enable"
setvar This command tells the printer to use it’s default address, if no address is
provided through DHCP or BOOTP. If you do not assign an IP address
after 2 minutes, the 10/100 Internal PS defaults to IP address
192.168.254.254.
Format:
! U1 setvar "wlan.ip.default_addr.enable" "value"
Values:
"on" = enabled
"off" = disabled
Default: "on"
Example • This setvar example shows the value set to "on".
! U1 setvar "wlan.ip.default_addr.enable" "on"
What the setvar value is set to is the getvar result. In this example, the getvar result is
"on".
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 270

270 SGD Wireless Commands
wlan.ip.dhcp.cid_all
wlan.ip.dhcp.cid_all
Description This printer setting defines the entire client identifier (DHCP option 61) if
DHCP is enabled on the wireless print server and "wlan.ip.dhcp.cid_type" is set to
"0", or "2". The MAC address is used if the type is set to "1". This command is supported
only on Zebra ZM400/ZM600™ and S4M™ printers.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the client identifier
prefix and suffix of the wireless print server.
Format:
! U1 getvar "wlan.ip.dhcp.cid_all"
setvar This command instructs the printer to change the client identifer prefix and
suffix of the wireless print server. The prefix gets cleared and the suffix
contains the entire client identifer.
Format:
! U1 setvar "wlan.ip.dhcp.cid_all" "value"
Values: A maximum length of 60 characters if the CID type is ASCII, or
120 characters if the CID type is hexadecimal.
Default Value: ""
Example • This setvar example shows the value set to "printer".
! U1 setvar "wlan.ip.dhcp.cid_all" "printer"
What the setvar value is set to is the getvar result. In this example, the getvar result is
"printer".
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 271

SGD Wireless Commands 271
wlan.ip.dhcp.cid_enable
wlan.ip.dhcp.cid_enable
Description This command determines if DHCP (option 61) is turned on or off of the
wireless print server. This command is supported only on Zebra ZM400/ZM600™ and S4M™
printers.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the status of the client
identifier of the wireless print server.
Format:
! U1 getvar "wlan.ip.dhcp.cid_enable"
setvar This command instructs the printer to set the status of the client identifier
of the wireless print server.
Format:
! U1 setvar "wlan.ip.dhcp.cid_enable" "value"
Values:
"off" = client identifier is turned off
"on" = client identifier is turned on
Default: "off"
Example • This setvar example shows the value set to "off".
! U1 setvar "wlan.ip.dhcp.cid_enable" "off"
What the setvar value is set to is the getvar result. In this example, the getvar result is
"off".
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 272

272 SGD Wireless Commands
wlan.ip.dhcp.cid_prefix
wlan.ip.dhcp.cid_prefix
Description This printer setting defines the prefix to be prepended to the DHCP client
identifier (option 61) when DHCP is enabled on the wireless print server and
"wlan.ip.dhcp.cid_type" is set to "0" or "2". This command is supported only on
Zebra ZM400/ZM600™ and S4M™ printers.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the client identifier
prefix of the wireless print server.
Format:
! U1 getvar "wlan.ip.dhcp.cid_prefix"
setvar This command instructs the printer to change the CID prefix of the
wireless print server.
Format:
! U1 setvar "wlan.ip.dhcp.cid_prefix" "value"
Values: Any text string up to 10 characters if the CID type is ASCII, or 20
characters if the CID type is hexadecimal.
Default Value: ""
Example • This setvar example shows the value set to "PRT001".
! U1 setvar "wlan.ip.dhcp.cid_prefix" "PRT001"
What the setvar value is set to is the getvar result. In this example, the getvar result is
"PRT001".
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 273

SGD Wireless Commands 273
wlan.ip.dhcp.cid_suffix
wlan.ip.dhcp.cid_suffix
Description This printer setting defines the unique suffix to be used as the client identifier
(DHCP option 61) if DHCP is enabled on the wireless print server and
"wlan.ip.dhcp.cid_type" is set to "0" or "2".
Type getvar; setvar
Important • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the client identifier
suffix on the wireless print server.
Format:
! U1 getvar "wlan.ip.dhcp.cid_suffix"
setvar This command instructs the printer to change the client identifier suffix
value on the wireless print server.
Format:
! U1 setvar "wlan.ip.dhcp.cid_suffix" "value"
Values: The maximum length of a value allowed is 60 ASCII characters
when the CID type is ASCII, or 120 hexadecimal values when the CID
type is hexadecimal.
Default Value: ""
Example • This setvar example shows the value set to "printer".
! U1 setvar "wlan.ip.dhcp.cid_suffix" "printer"
What the setvar value is set to is the getvar result. In this example, the getvar result is
"printer".
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 274

274 SGD Wireless Commands
wlan.ip.dhcp.cid_type
wlan.ip.dhcp.cid_type
Description This printer setting defines the type of client identifier (DHCP option 61) that
will be sent if DHCP is enabled on the wireless print server. A value of "1" means the type of
"Ethernet" and the printer’s MAC address will be used. A value of "0" or "2" means the
client identifier sent will be "wlan.ip.dhcp.cid_prefix" concatenated with
"wlan.ip.dhcp.cid_suffix". This command is supported only on Zebra
ZM400/ZM600™ and S4M™ printers.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the client identifier
type for the wireless print server.
Format:
! U1 getvar "wlan.ip.dhcp.cid_type"
setvar This command instructs the printer to enable "synthetic" client identifier
for the wireless print server.
Format:
! U1 setvar "wlan.ip.dhcp.cid_type" "value"
Values:
"0" = ASCII string
"1" = wireless radio card’s MAC address
"2" = HEX value
Default Value: "1"
Example • This setvar example shows the value set to "1".
! U1 setvar "wlan.ip.dhcp.cid_type" "1"
What the setvar value is set to is the getvar result. In this example, the getvar result is
"1".
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 275

SGD Wireless Commands 275
wlan.ip.gateway
wlan.ip.gateway
Description This command instructs the printer to change the wireless print server’s
gateway address. This command is supported only on Zebra ZM400/ZM600™ and S4M™
printers.
Type getvar; setvar
Note • This setting refers to the gateway address. A set value is ignored if the IP protocol is
not set to permanent.
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the wireless printer
server’s gateway address.
Format:
! U1 getvar "wlan.ip.gateway"
setvar This command instructs the printer to change the wireless printer server’s
gateway address.
Format: ! U1 setvar "wlan.ip.gateway" "value"
Values: Any valid gateway address
Default: "0.0.0.0"
Example • This setvar example shows the value set to "10.3.5.1".
! U1 setvar "wlan.ip.gateway" "10.3.5.1"
When the setvar value is set to "10.3.5.1", the getvar result is "10.3.5.1".
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 276

276 SGD Wireless Commands
wlan.ip.netmask
wlan.ip.netmask
Description This setting refers to the wireless print server’s subnet mask address. This value
is ignored if the IP protocol is not set to permanent. This command is supported only on Zebra
ZM400/ZM600™ and S4M™ printers.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with wireless print server’s
subnet mask.
Format: ! U1 getvar "wlan.ip.netmask"
setvar This command instructs the printer to change the wireless print servers’s
subnet mask.
Format: ! U1 setvar "wlan.ip.netmask" "value"
Values: Any valid subnet mask.
Default: "255.255.255.0"
Example • This setvar example shows the value set to "255.255.255.0".
! U1 setvar "wlan.ip.netmask" "255.255.255.0"
When the setvar value is set to "255.255.255.0", the getvar result is
"255.255.255.0".
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 277

SGD Wireless Commands 277
wlan.ip.port
wlan.ip.port
Description This printer setting refers to the wireless print server’s port number that the
TCP print service is listening on. Normal TCP communications from the host should be
directed to this port.This command is supported only on Zebra ZM400/ZM600™ and S4M™
printers.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the wireless printer
server’s TCP/UDP port number.
Format: ! U1 getvar "wlan.ip.port"
setvar This command instructs the printer to set the wireless print server’s
TCP/UDP port number.
Format: ! U1 setvar "wlan.ip.port" "value"
Values: 1 - 65535 (excluding any ports currently used by other services,
such as 21, 23, 80, and 515).
Default: "9100"
Example • This setvar example shows the value set to "9100".
! U1 setvar "wlan.ip.port" "9100"
When the setvar value is set to "9100", the getvar result is "9100".
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 278

278 SGD Wireless Commands
wlan.ip.protocol
wlan.ip.protocol
Description This command configures the IP addressing method used by the wireless print
server. This command is supported only on Zebra ZM400/ZM600™ and S4M™ printers.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the command for this format:
Commands Details
getvar This command returns the value of the currently selected IP protocol used
by the wireless print server.
Format: ! U1 getvar "wlan.ip.protocol"
setvar This command instructs the printer to configure the IP addressing method
used by the wireless print server.
Format:
! U1 setvar "wlan.ip.protocol" "value"
Values:
"bootp" = uses the standard bootp addressing method to obtain an
IP address and configuration
"dhcp" = uses the standard dhcp addressing method to obtain an IP
address and configuration for a server specified period of time
"rarp" = uses the standard rarp addressing method to obtain an IP
address
"glean" = uses the IP address from a PING packet that is sent to its
hardware address (unicast address)
“permanent” = uses static values assigned through other
commands
"all" = tries all of the dynamic addressing methods, not permanent,
to obtain an IP address
Default: "all"
Example • In this example, the setvar result is the current programming language that the
printer is using.
! U1 setvar "wlan.ip.protocol" "bootp"
What the setvar value is set to is the getvar result. In this example, the getvar result is
"bootp".
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 279

SGD Wireless Commands 279
wlan.ip.timeout.enable
wlan.ip.timeout.enable
Description This network setting refers to enabling the connection timeout on the wireless
print server. For this to take effect, the print server must be reset. This command is supported
only on Zebra ZM400/ZM600™ and S4M™ printers.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to return whether the timeout checking
is enabled on the wireless print server.
Format: ! U1 getvar "wlan.ip.timeout.enable"
setvar This command instructs the printer to enable or disable the timeout
checking on the wireless print server.
Format: ! U1 setvar "wlan.ip.timeout.enable" "value"
Values:
"off" = turns off the connection checking
"on" = turns on the connection checking
Default: "on"
Example • This setvar example shows the value set to "on".
! U1 setvar "wlan.ip.timeout.enable" "on"
When the setvar value is set to "on", the getvar result is "on".
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 280

280 SGD Wireless Commands
wlan.ip.timeout.value
wlan.ip.timeout.value
Description This network setting refers to the number of seconds before the connection
times out for the wireless print server. For this to take effect, the print server must be reset.
This command is supported only on Zebra ZM400/ZM600™ and S4M™ printers.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the time, in seconds,
before the connection times out.
Format: ! U1 getvar "wlan.ip.timeout.value"
setvar This command instructs the printer to set the the time value of the wireless
print server, in seconds, before the connection times out.
Format: ! U1 setvar "wlan.ip.timeout.value" "value"
Values: "0" through "3600"
Default: "300"
Example • This setvar example shows the value set to "300".
! U1 setvar "wlan.ip.timeout.value" "300"
When the setvar value is set to "300", the getvar result is "300".
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 281

SGD Wireless Commands 281
wlan.keep_alive.enable
wlan.keep_alive.enable
Description This setting controls the printers ability to send a LSAP (link service access
point) packet to the access point on an user controllable interval. This feature is included to
accommodate access points that require a regular confirmation that wireless clients are still
active.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the
wlan.keep_alive.enable setting.
Format: ! U1 getvar "wlan.keep_alive.enable"
setvar This command instructs the printer to send a LSAP (link service access
point) packet to the access point on an user controllable interval.
Format: ! U1 setvar "wlan.keep_alive.enable" "value"
Values:
"on" = turns on keep_alive
"off" = turns off keep_alive
Default: "on"
Example • This setvar example shows the value set to "on".
! U1 setvar "wlan.keep_alive.enable" "on"
When the setvar value is set to "on", the getvar result is "on".
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 282

282 SGD Wireless Commands
wlan.keep_alive.timeout
wlan.keep_alive.timeout
Description This printer setting manages the interval at which the LSAP (link service
access point) packet is sent.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the
wlan.keep_alive.timeout interval value.
Format: ! U1 getvar "wlan.keep_alive.timeout"
setvar This command instructs the printer to configure the frequency at which the
printer sends the wlan.keep_alive packet.
Format: ! U1 setvar "wlan.keep_alive.timeout" "value"
Values: 5 to 300 seconds
Default: "15"
Example • This setvar example shows the value set to "15".
! U1 setvar "wlan.keep_alive.timeout" "15"
When the setvar value is set to "15", the getvar result is "15".
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 283

SGD Wireless Commands 283
wlan.kerberos.kdc
wlan.kerberos.kdc
Description This printer setting refers to the Kerberos Key Distribution Center (KDC). The
KDC is a trusted server which maintains a database with account information for all security
principals (users) for a particular site or administrative domain (realm).
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the current Kerberos
KDC.
Format: ! U1 getvar "wlan.kerberos.kdc"
setvar This command instructs the printer to change the Kerberos KDC.
Format: ! U1 setvar "wlan.kerberos.kdc" "value"
Values: 0-32 ASCII characters
Default: "krbtgt"
Example • This setvar example shows the value set to "krbtgt".
! U1 setvar "wlan.kerberos.kdc" "krbtgt"
When the setvar value is set to "krbtgt", the getvar result is "krbtgt".
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 284

284 SGD Wireless Commands
wlan.kerberos.password
wlan.kerberos.password
Description This printer setting refers to the Kerberos password. The password must
correspond to a user profile established on the Kerberos KDC server in use.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the current Kerberos
password.
Format: ! U1 getvar "wlan.kerberos.password"
For protection a single "*" prints.
setvar This command instructs the printer to set the Kerberos password.
Format: ! U1 setvar "wlan.kerberos.password" "value"
Values: 0-32 alphanumeric characters
Default: "password"
Example • This setvar example shows the value set to "password".
! U1 setvar "wlan.kerberos.password" "password"
When the setvar value is set to "password", the getvar result is "*".
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 285

SGD Wireless Commands 285
wlan.kerberos.realm
wlan.kerberos.realm
Description This printer setting refers to the Kerberos realm, an administrative domain with
its own Kerberos server (KDC).
Type getvar; setvar
Important • If you are using a Windows 2000 Server the realm must be all upper-case. For
details, see the Windows 2000 Server example below.
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the current Kerberos
realm.
Format: ! U1 getvar "wlan.kerberos.realm"
setvar This command instructs the printer to change the Kerberos realm.
Format: ! U1 setvar "wlan.kerberos.realm" "value"
Values: 0-64 alphanumeric characters
Default: "kerberos"
Example • This setvar example shows the value set to "zebra".
! U1 setvar "wlan.kerberos.realm" "zebra"
When the setvar value is set to "zebra", the getvar result is "zebra".
Example • This setvar example shows the value set to "ZEBRA" on a Windows 2000
server.
! U1 setvar "wlan.kerberos.realm" "ZEBRA"
When the setvar value is set to "ZEBRA", the getvar result is "ZEBRA".
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


|  |  |
| --- | --- |
|  |  |


---

## Pagina 286

286 SGD Wireless Commands
wlan.kerberos.username
wlan.kerberos.username
Description This printer setting refers to the Kerberos user name. The user name must
correspond to a user profile established on the Kerberos KDC server in use.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the current Kerberos
user name.
Format: ! U1 getvar "wlan.kerberos.username"
setvar This command instructs the printer to change the Kerberos user name.
Format: ! U1 setvar "wlan.kerberos.username" "value"
Values: 0-32 alphanumeric characters
Default: "user"
Example • This setvar example shows the value set to "user".
! U1 setvar "wlan.kerberos.username" "user"
When the setvar value is set to "user", the getvar result is "user".
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 287

SGD Wireless Commands 287
wlan.mac_addr
wlan.mac_addr
Description This command retrieves the MAC address of the wireless print server. This
command is supported only on Zebra ZM400/ZM600™ printers.
Type getvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the command for this format:
Commands Details
getvar This command instructs the printer to respond with the MAC address of
the wireless print server.
Format: ! U1 getvar "wlan.mac_addr"
Example • In this example, the getvar result is the MAC address for the wireless print
server.
! U1 getvar "wlan.mac_addr"
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 288

288 SGD Wireless Commands
wlan.operating_mode
wlan.operating_mode
Description This printer setting refers to the network operating mode. Infrastructure mode
means that the printer will try to associate with an access point. Ad hoc mode means that the
printer will try to associate with a device other than an access point and join a standalone
network.
Type getvar; setvar
To use "ad hoc" mode configure the printer as follows:
• Set the ESSID to the new network’s ESSID.
• Turn off the DHCP and assign an IP Address to the printer.
• Set the subnet mask on the printer to the new network’s subnet mask.
• Change the operating mode on the printer to "ad hoc".
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the network-mode
value.
Format: ! U1 getvar "wlan.operating_mode"
setvar This command instructs the printer to set the network operating mode.
Format: ! U1 setvar "wlan.operating_mode" "value"
Values:
"adhoc" = printer will try to associate with a network device
"infrastructure" = printer will try to associate with an access
point
Default: "infrastructure"
Example • This setvar example shows the value set to "infrastructure".
! U1 setvar "wlan.operating_mode" "infrastructure"
When the setvar value is set to "infrastructure", the getvar result is
"infrastructure".
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 289

SGD Wireless Commands 289
wlan.password
wlan.password
Description This printer setting refers to the generic password that is used by the wireless
securities that need a password.
Type getvar; setvar
Important • Kerberos has its own password field
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with a generic password for
wireless securities.
Format: ! U1 getvar "wlan.password"
For protection a single "*" prints.
setvar This command instructs the printer to set a generic password for the
wireless securities that need a password.
Format: ! U1 setvar "wlan.password" "value"
Values: A maximum of 32 alphanumeric characters.
Default: "password"
Example • This setvar example shows the value set to "password".
! U1 setvar "wlan.password" "password"
When the setvar value is set to "password", the getvar result is "*".
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 290

290 SGD Wireless Commands
wlan.preamble
wlan.preamble
Description This printer setting selects the radio preamble length to be used.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the current preamble
length.
Format: ! U1 getvar "wlan.preamble"
setvar This command instructs the printer to set the preamble length.
Format: ! U1 setvar "wlan.preamble" "value"
Values:
"long" = enables long preamble
"short" = enables short preamble
Default: "long"
Example • This setvar example shows the value set to "long".
! U1 setvar "wlan.preamble" "long"
When the setvar value is set to "long", the getvar result is "long".
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 291

SGD Wireless Commands 291
wlan.private_key_password
wlan.private_key_password
Description This printer setting allows the setting of the optional private key password.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the value of the private
key password.
Format: ! U1 getvar "wlan.private_key_password"
For protection a single "*" prints.
setvar This command instructs the printer to set the private key password.
Format: ! U1 setvar "wlan.private_key_password"
"value"
Values: A maximum of 32 alphanumeric characters
Default: ""
Example • This setvar example shows the value set to "password".
! U1 setvar "wlan.private_key_password" "password"
When the setvar value is set to "password", the getvar result is "*".
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 292

292 SGD Wireless Commands
wlan.roam.interval
wlan.roam.interval
Description This printer setting refers to specifying the wireless roam interval.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the specified roam
interval.
Format: ! U1 getvar "wlan.roam.interval"
setvar This command instructs the printer to set the wireless roam interval.
Format: ! U1 setvar "wlan.roam.interval" "value"
Values: Decimal values between 5 and 255 inclusive
Default: "20"
Example • This setvar example shows the value set to "20".
! U1 setvar "wlan.roam.interval" "20"
When the setvar value is set to "20", the getvar result is "20".
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 293

SGD Wireless Commands 293
wlan.roam.signal
wlan.roam.signal
Description This printer setting refers to specifying the wireless roam signal.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the specified wireless
roam signal.
Format: ! U1 getvar "wlan.roam.signal"
setvar This command instructs the printer to set the wireless roam signal.
Format: ! U1 setvar "wlan.roam.signal" "value"
Values: Decimal values between 1 and 75 inclusive.
Default: "50"
Example • This setvar example shows the value set to "50".
! U1 setvar "wlan.roam.signal" "50"
When the setvar value is set to "50", the getvar result is "50".
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 294

294 SGD Wireless Commands
wlan.security
wlan.security
Description This printer setting allows you to specify both the wireless encryption type and
authentication type in one command.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
Note • When using certificate files, Zebra printers support:
• using Privacy Enhanced Mail (PEM) formatted certificate files.
• using the client certificate and private key as two files, each downloaded separately.
• using exportable PAC files for EAP-FAST.
These certificate files can only be sent using ZPL, not SGD. The ZPL command to use when
sending these certificate files is the ~DY command.
14245L-001 Rev. A EPL Programming Guide 5/30/07

---

## Pagina 295

SGD Wireless Commands 295
wlan.security
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to return the name and not the type. If
an invalid security mode is entered the printer returns Invalid Mode.
Format: ! U1 getvar "wlan.security" "value"
setvar This command instructs the printer to set the wireless security value.
Format: ! U1 setvar "wlan.security" "value"
Values:
"1" = No Security or "none"
"2" = WEP 40-bit or "wep 40-bit"
"3" = WEP 128-bit or "wep 128-bit"
"4" = EAP-TLS or "eap-tls"
"5" = EAP-TTLS or "eap-ttls"
"6" = EAP-FAST or "eap-fast"
"7" = PEAP or "peap"
"8" = LEAP or "leap"
"9" = WPA PSK or "wpa psk"
"10" = WPA EAP-TLS or "wpa eap-tls"
"11" = WPA EAP-TTLS or "wpa eap-ttls"
"12" = WPA EAP-FAST or "wpa eap-fast"
"13" = WPA PEAP or "wpa peap"
"14" = WPA LEAP or "wpa leap"
Default: "1"
Example • This setvar example shows the value set to "1".
! U1 setvar "wlan.security" "1"
When the setvar value is set to "1", the getvar result is "none".
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 296

296 SGD Wireless Commands
wlan.signal_noise
wlan.signal_noise
Description This command returns the signal noise on the wireless network. Values above
40% represent a very a very significant noise, and radio communication is not reliable.
Type getvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the command for this format:
Commands Details
getvar This command instructs the printer to return the current signal noise on the
wireless network.
Format: ! U1 getvar "wlan.signal_noise"
Example • In this example, the getvar result is the current signal_noise value.
! U1 getvar "wlan.signal_noise"
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 297

SGD Wireless Commands 297
wlan.signal_quality
wlan.signal_quality
Description This command instructs the printer to return the current signal quality of the
wireless network. Values below 40% represent a poor signal quality, and radio communication
is not reliable.
Type getvar
Note • .These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the command for this format:
Commands Details
getvar This command instructs the printer to return the current signal quality of
the wireless network.
Format: ! U1 getvar "wlan.signal_quality"
Example • In this example, the getvar result is the current signal_quality value.
! U1 getvar "wlan.signal_quality"
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 298

298 SGD Wireless Commands
wlan.signal_strength
wlan.signal_strength
Description This command returns the signal strength of the connection to the access point
as a percentage value between zero (not connected) and 100 (strongest signal). Values below
40% represent a very poor signal and radio communication is not reliable.
Type getvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the command for this format:
Commands Details
getvar This command instructs the printer to respond with the current signal
strength.
Format: ! U1 getvar "wlan.signal_strength"
Example • In this example, the getvar result is "93".
! U1 getvar "wlan.signal_strength"
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 299

SGD Wireless Commands 299
wlan.station_name
wlan.station_name
Description This printer setting refers to the station name.
Type setvar;getvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the station name value.
Format: ! U1 getvar "wlan.station_name"
setvar This command instructs the printer to set the station name.
Format: ! U1 setvar "wlan.station_name" "value"
Values: A maximum of 32 alphanumeric characters
Default: "ZEBRA"
Example • This setvar example shows the value set to "ZEBRA".
! U1 setvar "wlan.station_name" "ZEBRA"
When the setvar value is set to "ZEBRA", the getvar result is "ZEBRA".
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 300

300 SGD Wireless Commands
wlan.tx_power
wlan.tx_power
Description This printer setting refers to specifying the wireless transmit power.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the wireless transmit
power.
Format: ! U1 getvar "wlan.tx_power"
setvar This command instructs the printer to set the wireless transmit power.
Format: ! U1 setvar "wlan.tx_power" "value"
Values: Decimal values of 1, 5, 20, 30, 50, 100
Default: "100"
Example • This setvar example shows the value set to "100".
! U1 setvar "wlan.tx_power" "100"
When the setvar value is set to "100", the getvar result is "100".
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 301

SGD Wireless Commands 301
wlan.tx_rate
wlan.tx_rate
Description This printer setting refers to specifying the wireless transmit rate.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the wireless transmit
rate.
Format: ! U1 getvar "wlan.tx_rate"
setvar This command instructs the printer to set the wireless transmit rate.
Format: ! U1 setvar "wlan.tx_rate" "value"
Values: 1, 2, 5.5, 11, all
Default: "all"
Example • This setvar example shows the value set to "all".
! U1 setvar "wlan.tx_rate" "all"
When the setvar value is set to "all", the getvar result is "all".
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 302

302 SGD Wireless Commands
wlan.username
wlan.username
Description This printer setting refers to the generic user name that is used by the wireless
securities that need a user name.
Type getvar; setvar
Important • Kerberos has its own user name field.
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with a generic user name for
the wireless securities that need a user name.
Format: ! U1 getvar "wlan.username"
setvar This command instructs the printer to set a generic user name for wireless
securities that need a user name.
Format: ! U1 setvar "wlan.username" "value"
Values: A maximum of 32 alphanumeric characters
Default: "user"
Example • This setvar example shows the value set to "user".
! U1 setvar "wlan.username" "user"
When the setvar value is set to "user", the getvar result is "user".
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 303

SGD Wireless Commands 303
wlan.wep.auth_type
wlan.wep.auth_type
Description For the WEP security type, this printer setting selects the authentication type to
be used between the printer and the access point. The authentication types are open system and
shared key.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to retrieve the current WEP
authentication type.
Format: ! U1 getvar "wlan.wep.auth_type"
setvar This command instructs the printer to set the WEP authentication type.
Format: ! U1 setvar "wlan.wep.auth_type" "value"
Values:
"open" = enables the open authentication type
"shared" = enables the shared authentication type
Default: "open"
Example • This setvar example shows the value set to "open".
! U1 setvar "wlan.wep.auth_type" "open"
When the setvar value is set to "open", the getvar result is "open".
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 304

304 SGD Wireless Commands
wlan.wep.index
wlan.wep.index
Description This printer setting refers to the WEP (Wired Equivalent Privacy) encryption
key index. This printer setting determines which one of the four encryption keys is to be used
by the client (printer).
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the encryption key
index.
Format: ! U1 getvar "wlan.wep.index"
setvar This command instructs the printer to set the encryption key index.
Format: ! U1 setvar "wlan.wep.index" "value"
Values:
"1" = enables encryption key 1
"2" = enables encryption key 2
"3" = enables encryption key 3
"4" = enables encryption key 4
Default: "1"
Example • This setvar example shows the value set to "1".
! U1 setvar "wlan.wep.index" "1"
When the setvar value is set to "1", the getvar result is "1".
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 305

SGD Wireless Commands 305
wlan.wep.key1
wlan.wep.key1
Description This printer setting refers to the first indexed WEP encryption key. The WEP
encryption key is a hexadecimal or string value. This key should match the wireless network
WEP encryption key 1.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the encryption key.
Format: ! U1 getvar "wlan.wep.key1"
For protection a single "*" prints.
setvar This command instructs the printer to set the encryption key.
Format: ! U1 setvar "wlan.wep.key1" "value"
Values:
10 hexadecimal characters for 40-bit encryption
26 hexadecimal characters for 128-bit encryption
Default: All zeros
Example • This setvar example shows the value set to "A1B2C3D4F5".
! U1 setvar "wlan.wep.key1" "A1B2C3D4F5"
When the setvar value is set to "A1B2C3D4F5", the getvar result is "*".
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 306

306 SGD Wireless Commands
wlan.wep.key2
wlan.wep.key2
Description This printer setting refers to the second indexed WEP encryption key. The
WEP encryption key is a hexadecimal string value. This key should match the wireless
network WEP encryption key 2.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer respond with the encryption key.
Format: ! U1 getvar "wlan.wep.key2"
For protection a single "*" prints.
setvar This command instructs the printer to set the encryption key.
Format: ! U1 setvar "wlan.wep.key2" "value"
Values:
10 hexadecimal characters for 40-bit encryption
26 hexadecimal characters for 128-bit encryption
Default: All zeros
Example • This setvar example shows the value set to "A1B2C3D4F5".
! U1 setvar "wlan.wep.key2" "A1B2C3D4F5"
When the setvar value is set to "A1B2C3D4F5", the getvar result is "*".
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 307

SGD Wireless Commands 307
wlan.wep.key3
wlan.wep.key3
Description This printer setting refers to the third indexed WEP encryption key. The WEP
encryption key is a hexadecimal string value. This key should match the wireless network
WEP encryption key 3.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the encryption key.
Format: ! U1 getvar "wlan.wep.key3"
Format: For protection a single "*" prints.
setvar This command instructs the printer to set the encryption key.
Format: ! U1 setvar "wlan.wep.key3" "value"
Values:
10 hexadecimal characters for 40-bit encryption
26 hexadecimal characters for 128-bit encryption
Default: All zeros
Example • This setvar example shows the value set to "A1B2C3D4F5".
! U1 setvar "wlan.wep.key3" "A1B2C3D4F5"
When the setvar value is set to "A1B2C3D4F5", the getvar result is "*".
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 308

308 SGD Wireless Commands
wlan.wep.key4
wlan.wep.key4
Description This printer setting refers to the fourth indexed WEP encryption key. The WEP
encryption key is a hexadecimal string value. This key should match the wireless network
WEP encryption key 4.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer respond with the encryption key.
Format: ! U1 getvar "wlan.wep.key4"
For protection a single "*" prints.
setvar This command instructs the printer to set the encryption key.
Format: ! U1 setvar "wlan.wep.key4" "value"
Values:
10 hexadecimal characters for 40-bit encryption
26 hexadecimal characters for 128-bit encryption
Default: All zeros
Example • This setvar example shows the value set to "A1B2C3D4F5".
! U1 setvar "wlan.wep.key4" "A1B2C3D4F5"
When the setvar value is set to "A1B2C3D4F5", the getvar result is "*".
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 309

SGD Wireless Commands 309
wlan.wep.key_format
wlan.wep.key_format
Description This printer setting specifies the format for the WEP key.
Type getvar; setvar
Important • This printer setting should proceed any of the wep.key settings if you select a
non-default value.
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the WEP key format.
Format: ! U1 getvar "wep.key_format"
setvar This command instructs the printer to set the WEP key format.
Format: ! U1 setvar "wlan.wep.key_format" "value"
Values:
"ascii" = WEP key is set by ASCII string
"hex" = WEP key is a Hex string
Default: "hex"
Example • This setvar example shows the value set to "ascii".
! U1 setvar "wlan.wep.key_format" "ascii"
When the setvar value is set to "ascii", the getvar result is "ascii".
5/30/07 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 310

310 SGD Wireless Commands
wlan.wpa.psk
wlan.wpa.psk
Description This printer setting specifies the pre-shared key (PSK) value to use when the
WPA authentication is set to PSK.
Type getvar; setvar
Note • These are key reference links for this command:
• For details on SGD command structure, see Command Structure onpage246.
• For details on the syntax and use of SGD commands, see SGD Command Support
onpage311.
This table identifies the commands for this format:
Commands Details
getvar This command instructs the printer to respond with the pre-shared key.
Format: ! U1 getvar "wlan.wpa.psk"
For protection a single "*" prints.
setvar This command instructs the printer to set the pre-shared key.
Format: ! U1 setvar "wlan.wpa.psk" "value"
Values: 64 hexadecimal digits
Default: 64 zeros (00000000...)
Example • This setvar example shows the value set to "00000000...".
! U1 setvar "wlan.wpa.psk" "00000000..."
When the setvar value is set to "00000000...", the getvar result is "*".
14245L-001 Rev. A EPL Programming Guide 5/30/07

|  |  |
| --- | --- |
|  |  |


---

## Pagina 311

5/30/07 EPL Programming Guide 14245L-001 Rev. A
A
SGD Command Support
This appendix provides you with details identifying which SGD commands can be used with
the wired and wireless print server interfaces.

|  |
| --- |
|  |


---

## Pagina 312

312 SGD Command Support
Printer and Firmware Compatibility
14245L-001 Rev. A EPL Programming Guide 5/30/07
Printer and Firmware Compatibility
Table7 identifies the supported printer model, firmware version, and command type (internal
wired, external wired, or wireless) for all SGD commands. In order to set and get
configurations, the noted supported SGD commands can also be used through the serial,
parallel, and USB interfaces.
Important • All the firmware versions noted in Table7, refer to minimum firmware version
required.
Table 7 • SGD Command Syntax
Key: Internal Wired = (cid:141) External Wired = (cid:132) Wireless = (cid:97)
SGD Commands S4M / ZM400 / ZM600
appl.bootblock (cid:141)(cid:132)(cid:97) E53.15.0
appl.name (cid:141)(cid:132)(cid:97) E53.15.0
card.inserted (cid:97) E53.15.0
card.mac_addr (cid:97) E53.15.0
device.languages (cid:141)(cid:132)(cid:97) E53.15.0
external_wired.ip.addr (cid:132) E53.15.0
external_wired.ip.arp_interval (cid:132) E53.15.0
external_wired.ip.default_addr.enable (cid:132) E53.15.0
external_wired.ip.dhcp.cid_all (cid:132) E53.15.0
external_wired.ip.dhcp.cid_enable (cid:132) E53.15.0
external_wired.ip.dhcp.cid_prefix (cid:132) E53.15.0
external_wired.ip.dhcp.cid_suffix (cid:132) E53.15.0
external_wired.ip.dhcp.cid_type (cid:132) E53.15.0
external_wired.ip.gateway (cid:132) E53.15.0
external_wired.ip.netmask (cid:132) E53.15.0
external_wired.ip.port (cid:132) E53.15.0
external_wired.ip.protocol (cid:132) E53.15.0
external_wired.ip.timeout.enable (cid:132) E53.15.0
external_wired.ip.timeout.value (cid:132) E53.15.0
external_wired.mac_addr (cid:132) E53.15.0
internal_wired.ip.addr (cid:141) E53.15.0
internal_wired.ip.arp_interval (cid:141) E53.15.0
internal_wired.ip.default_addr.enable (cid:141) E53.15.0
internal_wired.ip.dhcp.cid_all (cid:141) E53.15.0
internal_wired.ip.dhcp.cid_enable (cid:141) E53.15.0

---

## Pagina 313

SGD Command Support 313
Printer and Firmware Compatibility
5/30/07 EPL Programming Guide 14245L-001 Rev. A
Table 7 • SGD Command Syntax
Key: Internal Wired = (cid:141) External Wired = (cid:132) Wireless = (cid:97)
SGD Commands S4M / ZM400 / ZM600
internal_wired.ip.dhcp.cid_prefix (cid:141) E53.15.0
internal_wired.ip.dhcp.cid_suffix (cid:141) E53.15.0
internal_wired.ip.dhcp.cid_type (cid:141) E53.15.0
internal_wired.ip.gateway (cid:141) E53.15.0
internal_wired.ip.netmask (cid:141) E53.15.0
internal_wired.ip.port (cid:141) E53.15.0
internal_wired.ip.protocol (cid:141) E53.15.0
internal_wired.ip.timeout.enable (cid:141) E53.15.0
internal_wired.ip.timeout.value (cid:141) E53.15.0
internal_wired.mac_addr (cid:141) E53.15.0
ip.active_network (cid:141)(cid:132)(cid:97) E53.15.0
ip.addr (cid:97) E53.15.0
ip.arp_interval (cid:141)(cid:97) E53.15.0
ip.bootp.enable (cid:141)(cid:132)(cid:97) E53.15.0
ip.dhcp.cid_all (cid:141)(cid:97) E53.15.0
ip.dhcp.cid_enable (cid:141)(cid:97) E53.15.0
ip.dhcp.cid_prefix (cid:141)(cid:97) E53.15.0
ip.dhcp.cid_suffix (cid:141)(cid:97) E53.15.0
ip.dhcp.cid_type (cid:141)(cid:97) E53.15.0
ip.dhcp.enable (cid:141)(cid:132)(cid:97) E53.15.0
ip.ftp.enable (cid:141)(cid:132)(cid:97) E53.15.0
ip.gateway (cid:141)(cid:97) E53.15.0
ip.http.enable (cid:141)(cid:132)(cid:97) E53.15.0
ip.lpd.enable (cid:141)(cid:132)(cid:97) E53.15.0
ip.netmask (cid:97) E53.15.0
ip.pop3.enable (cid:141)(cid:132)(cid:97) E53.15.0
ip.pop3.password (cid:141)(cid:132)(cid:97) E53.15.0
ip.pop3.poll (cid:141)(cid:132)(cid:97) E53.15.0
ip.pop3.server_addr (cid:141)(cid:132)(cid:97) E53.15.0
ip.pop3.username (cid:141)(cid:132)(cid:97) E53.15.0
ip.port (cid:97) E53.15.0
ip.primary_network (cid:97) E53.15.0

---

## Pagina 314

314 SGD Command Support
Printer and Firmware Compatibility
14245L-001 Rev. A EPL Programming Guide 5/30/07
Table 7 • SGD Command Syntax
Key: Internal Wired = (cid:141) External Wired = (cid:132) Wireless = (cid:97)
SGD Commands S4M / ZM400 / ZM600
ip.smtp.domain (cid:141)(cid:132)(cid:97) E53.15.0
ip.smtp.enable (cid:141)(cid:132)(cid:97) E53.15.0
ip.smtp.server_addr (cid:141)(cid:132)(cid:97) E53.15.0
ip.snmp.enable (cid:141)(cid:132)(cid:97) E53.15.0
ip.snmp.get_community_name (cid:141)(cid:132)(cid:97) E53.15.0
ip.snmp.set_community_name (cid:141)(cid:132)(cid:97) E53.15.0
ip.tcp.enable (cid:141)(cid:132)(cid:97) E53.15.0
ip.telnet.enable (cid:141)(cid:132)(cid:97) E53.15.0
ip.udp.enable (cid:141)(cid:132)(cid:97) E53.15.0
media.printmode (cid:141)(cid:132)(cid:97) E53.15.0
odometer.headclean (cid:141)(cid:132)(cid:97) E53.15.0
odometer.headnew (cid:141)(cid:132)(cid:97) E53.15.0
odometer.label_dot_length (cid:141)(cid:132)(cid:97) E53.15.0
odometer.media_marker_count1 (cid:141)(cid:132)(cid:97) E53.15.0
odometer.media_marker_count2 (cid:141)(cid:132)(cid:97) E53.15.0
odometer.total_print_length (cid:141)(cid:132)(cid:97) E53.15.0
wlan.adhocautomode (cid:97) E53.15.0
wlan.adhocchannel (cid:97) E53.15.0
wlan.associated (cid:97) E53.15.0
wlan.channel_mask (cid:97) E53.15.0
wlan.essid (cid:97) E53.15.0
wlan.firmware_version (cid:97) E53.15.0
wlan.ip.addr (cid:97) E53.15.0
wlan.ip.arp_interval (cid:97) E53.15.0
wlan.ip.default_addr.enable (cid:97) E53.15.0
wlan.ip.dhcp.cid_all (cid:97) E53.15.0
wlan.ip.dhcp.cid_enable (cid:97) E53.15.0
wlan.ip.dhcp.cid_prefix (cid:97) E53.15.0
wlan.ip.dhcp.cid_suffix (cid:97) E53.15.0
wlan.ip.dhcp.cid_type (cid:97) E53.15.0
wlan.ip.gateway (cid:97) E53.15.0
wlan.ip.netmask (cid:97) E53.15.0

---

## Pagina 315

SGD Command Support 315
Printer and Firmware Compatibility
5/30/07 EPL Programming Guide 14245L-001 Rev. A
Table 7 • SGD Command Syntax
Key: Internal Wired = (cid:141) External Wired = (cid:132) Wireless = (cid:97)
SGD Commands S4M / ZM400 / ZM600
wlan.ip.port (cid:97) E53.15.0
wlan.ip.protocol (cid:97) E53.15.0
wlan.ip.timeout.enable (cid:97) E53.15.0
wlan.ip.timeout.value (cid:97) E53.15.0
wlan.keep_alive.enable (cid:97) E53.15.0
wlan.keep_alive.timeout (cid:97) E53.15.0
wlan.kerberos.kdc (cid:97) E53.15.0
wlan.kerberos.password (cid:97) E53.15.0
wlan.kerberos.realm (cid:97) E53.15.0
wlan.kerberos.username (cid:97) E53.15.0
wlan.mac_addr (cid:97) E53.15.0
wlan.operating_mode (cid:97) E53.15.0
wlan.password (cid:97) E53.15.0
wlan.preamble (cid:97) E53.15.0
wlan.private_key_password (cid:97) E53.15.0
wlan.roam.interval (cid:97) E53.15.0
wlan.roam.signal (cid:97) E53.15.0
wlan.security (cid:97) E53.15.0
wlan.signal_noise (cid:97) E53.15.0
wlan.signal_quality (cid:97) E53.15.0
wlan.signal_strength (cid:97) E53.15.0
wlan.station_name (cid:97) E53.15.0
wlan.tx_power (cid:97) E53.15.0
wlan.tx_rate (cid:97) E53.15.0
wlan.username (cid:97) E53.15.0
wlan.wep.auth_type (cid:97) E53.15.0
wlan.wep.index (cid:97) E53.15.0
wlan.wep.key_format (cid:97) E53.15.0
wlan.wep.key1 (cid:97) E53.15.0
wlan.wep.key2 (cid:97) E53.15.0
wlan.wep.key3 (cid:97) E53.15.0

---

## Pagina 316

316 SGD Command Support
Printer and Firmware Compatibility
14245L-001 Rev. A EPL Programming Guide 5/30/07
Table 7 • SGD Command Syntax
Key: Internal Wired = (cid:141) External Wired = (cid:132) Wireless = (cid:97)
SGD Commands S4M / ZM400 / ZM600
wlan.wep.key4 (cid:97) E53.15.0
wlan.wpa.psk (cid:97) E53.15.0

---

## Pagina 317

B
Character References
This section has character references.
Resident Fonts 1-5
Page Mode supports 5 different font sizes, numbered 1-5. Each font can be expanded both
horizontally and vertically. All fonts are non-proportional, mono-spaced. The ASCII value of
each character is dependent on the I command character set (code page) selection.
Figure 7 • Resident Fonts Example
05/30/2007 EPL Programming Guide 14245L-001 Rev. A

|  |
| --- |
|  |


---

## Pagina 318

318 Character References
Default Character Map Code Page 437
Default Character Map Code Page 437
Fonts 1-4 are represented by the shaded and unshaded cells and Font 5 only supports shaded
cells. See the. The electronic manual version on the user's CD includes links to the individual
code pages.
Figure 8 • Code Page 437
Euro Character
Page mode printing supports the Euro character with a character substitution command, the
oR command. The Font 5 character set does not support the typical implementation of the
Euro character position at 213 decimal (D5 hexadecimal).
14245L-001 Rev. A EPL Programming Guide 05/30/2007

---

## Pagina 319

Character References 319
Dump Mode Character Map
Dump Mode Character Map
The printer’s Dump Mode is used to troubleshoot programming. The printer will print all data
as sent in Dump Mode Characters, shown below.
Figure 9 • Dump Mode Character Map
05/30/2007 EPL Programming Guide 14245L-001 Rev. A

---

## Pagina 320

320 Character References
Dump Mode Character Map
Notes • ___________________________________________________________________
__________________________________________________________________________
__________________________________________________________________________
__________________________________________________________________________
__________________________________________________________________________
__________________________________________________________________________
__________________________________________________________________________
__________________________________________________________________________
__________________________________________________________________________
__________________________________________________________________________
14245L-001 Rev. A EPL Programming Guide 05/30/2007

---

## Pagina 321

C
2746e Print Odometer
The 2746e printer includes additional hardware for a print odometer to assist with printer
maintenance and maintenance scheduling. The print head, as a consumable item, wears out
and may need periodic replacement to maintain print quality. The printer also needs regular
cleaning and print head conditioning to maximize the service life of the printer and it’s print
head. The print odometer can assist with printer maintenance and scheduling.
The printer has the ability to report serial number, distance printed by the print head and total
distance printed by the printer.
The Real Time Clock (RTC) option adds the ability to record the date with the print odometer
data.
The printer has the ability to report when the properly maintained print head has reached the
end its usable life cycle. By default, this feature is disabled and must be activated with EPL2
programming commands. The end of print head life message can also be customized to add
service contact information.
05/30/2007 EPL Programming Guide 14245L-001 Rev. A

|  |
| --- |
|  |


---

## Pagina 322

322 2746e Print Odometer
ELP2 Odometer Commands
ELP2 Odometer Commands
The print odometer data is accessed and controlled via EPL2 Page Mode programming
commands. The odometer commands are:
URH command - Prints or reports via serial or USB interfaces a print head history report for
up to ten print heads.
URL command - Prints or reports via serial or USB interfaces the current print head or total
print distance of media run on the printer.
URR command - Prints or reports via serial or USB interfaces a Print Head Life Reminder
status report. The report may include a custom message stored by the oL command, to be
displayed when a reminder is issued (default is PRINTHEAD LIFE EXCEEDED), the
distance needed to activate print head life reminder (warning) label, and the frequency of
reminder labels after the reminder is on and in effect.
oL, oLn and oLy commands - Set and control the print head life (exceeded) reminder
label’s parameters and reporting status. See the URR command description, above.
14245L-001 Rev. A EPL Programming Guide 05/30/2007

---

## Pagina 323

2746e Print Odometer 323
oL
oL
Print Head Life Reminder Control
Description Use this command to customize the print head life reminder report. See the
URR command to check the settings and status.
Syntax oL[p ,p ,p
1 2 3]
Parameters This table identifies the parameters for this format:
Parameters Details
No parameters Resets the parameters to their default values for print head life
reminder reporting.
p = Reminder Threshold Sets the distance to be printed before a reminder label
1
warning can be issued. Omitting or setting the value to 0will
reset it to the default value of 50 kilometers.
Default Value: 50 kilometers
Accepted Values: 1–255; 1 = 1 kilometer
p = Frequency of Sets the number of labels to be printed prior to issuing
2
Reminder another reminder label.
Omitting the parameter will reset it to the default. The 0 value
causes the printer with the reminder enabled and in effect, to
report a Reminder Message at power-up, error recovery, after
a media out condition, a reset or after Cancel has been pressed
on the printer.
Default Value: 0 labels
Accepted Values: 1–255; 1 = 1 label printed
p = Message “DATA” Default Value: PRINTHEAD LIFE EXCEEDED
3
Represents a fixed data field for a message of 39 characters or
less. The message “DATA” is bound by quotes. See the EPL2
programming ASCII Text (A) and the Character Set Selection
commands for details on printing text.
Example •
oL40 ; Sets the threshold to 40km, frequency and message are default
oL,5 ; Sets the frequency to 5 labels, threshold and message are default
oL,,”Replace the print head”
; Sets the message to Replace the print head, threshold and
; frequency are default
05/30/2007 EPL Programming Guide 14245L-001 Rev. A

---

## Pagina 324

324 2746e Print Odometer
oLn
oLn
Disable Print Head Life Reminder
Description Use this command to deactivate the print head life reminder reporting. Use the
URR command to check the Print Head Life Reminder settings.
Syntax oLn
Parameters There are no parameters for this format.
14245L-001 Rev. A EPL Programming Guide 05/30/2007

---

## Pagina 325

2746e Print Odometer 325
oLy
oLy
Enable Print Head Life Reminder
Description Use this command to activate the print head life reminder reporting. Use the
URR command to check the Print Head Life Reminder settings.
Syntax oLy
Parameters There are no parameters for this format.
05/30/2007 EPL Programming Guide 14245L-001 Rev. A

---

## Pagina 326

326 2746e Print Odometer
URH
URH
Print Head History Report
Description Use this command to print or report (via the serial or USB interface) a print
head history report of the distance printed by the last twenty (20) recorded print heads or the
last ten (10) recorded print heads if the RTC is in use.
Syntax URH[p ,p
1 2]
Parameters This table identifies the parameters for this format. Parameters are optional and
maybe entered and any order.
Parameters Details
p = Report Method Sends information back to the host via the serial port and the
1
(device) last active bi-directional interface.
p = Printer
s = Serial
Default Value: s
p = Units m = Millimeters
2
i = Inches
Example • (default)
(cid:41)(cid:38)(cid:34)(cid:37)(cid:1)(cid:45)(cid:42)(cid:39)(cid:38)(cid:1)(cid:41)(cid:42)(cid:52)(cid:53)(cid:48)(cid:51)(cid:58)(cid:1)(cid:1)(cid:39)(cid:48)(cid:51)(cid:1)(cid:52)(cid:16)(cid:47)(cid:1)(cid:57)(cid:57)(cid:57)(cid:57)(cid:57)(cid:57)(cid:57)(cid:57)(cid:57)(cid:57)
(cid:11)(cid:11)(cid:11)(cid:11)(cid:11)(cid:1)(cid:52)(cid:38)(cid:51)(cid:42)(cid:34)(cid:45)(cid:1)(cid:47)(cid:54)(cid:46)(cid:35)(cid:38)(cid:51)(cid:1)(cid:46)(cid:42)(cid:52)(cid:46)(cid:34)(cid:53)(cid:36)(cid:41)(cid:1)(cid:11)(cid:11)(cid:11)(cid:11)(cid:11)
(cid:1)(cid:4)(cid:1)(cid:1)(cid:1)(cid:1)(cid:1)(cid:37)(cid:42)(cid:52)(cid:53)(cid:34)(cid:47)(cid:36)(cid:38)
(cid:17)(cid:17)(cid:18)(cid:1)(cid:57)(cid:57)(cid:57)(cid:13)(cid:57)(cid:57)(cid:57)(cid:13)(cid:57)(cid:57)(cid:57)(cid:3)(cid:1)(cid:1)(cid:1)(cid:60)(cid:57)(cid:57)(cid:57)(cid:1)(cid:69)(cid:66)(cid:90)(cid:84)(cid:62)
(cid:17)(cid:17)(cid:19)(cid:1)(cid:57)(cid:57)(cid:57)(cid:13)(cid:57)(cid:57)(cid:57)(cid:13)(cid:57)(cid:57)(cid:57)(cid:3)(cid:1)(cid:1)(cid:1)(cid:60)(cid:57)(cid:57)(cid:57)(cid:1)(cid:69)(cid:66)(cid:90)(cid:84)(cid:62)
(cid:17)(cid:17)(cid:20)(cid:1)(cid:57)(cid:57)(cid:57)(cid:13)(cid:57)(cid:57)(cid:57)(cid:13)(cid:57)(cid:57)(cid:57)(cid:3)(cid:1)(cid:1)(cid:1)(cid:60)(cid:57)(cid:57)(cid:57)(cid:1)(cid:69)(cid:66)(cid:90)(cid:84)(cid:62)
(cid:17)(cid:17)(cid:21)(cid:1)(cid:57)(cid:57)(cid:57)(cid:13)(cid:57)(cid:57)(cid:57)(cid:13)(cid:57)(cid:57)(cid:57)(cid:3)(cid:1)(cid:1)(cid:1)
For units with the RTC option installed: The **SERIAL NUMBER MISMATCH ** line
prints when the serial number stored in the RTC does not match the serial number stored on
the printer’s main PCBA. One or both the PCBA or RTC has been changed in this unit and
the odometer data does not accurately represent printer usage.
The last print head record does not include the number of days in service.
14245L-001 Rev. A EPL Programming Guide 05/30/2007

|  |  |
| --- | --- |
|  |  |


---

## Pagina 327

2746e Print Odometer 327
URL
URL
Read Print Odometers
Description Use this command to print or report (via the serial or USB interface) latest
active print odometer data stored in printer memory. Report printer usage for one or both of the
following:
• The current distance printed by the presently installed print head.
• The total distance printed by the printer.
Syntax URL[p ,p ,p ]
1 2 3
Parameters This table identifies the parameters for this format. Parameters are optional and
maybe entered and any order.
Parameters Details
No parameters This is the default value. Prints both Head Life and Total Print
Distance report data strings.
p = Report Method Sends information back to the host via the serial port and the
1
(device) last active bi-directional interface.
p = Printer
s = Serial
Default Value: s
p = Units m = Millimeters
2
i = Inches
p = Read Meter h = Head Life (distance)
3
t = Total Print Distance
Important • Do not use both the p parameters,
3
h and t.
None = Default. Prints both head Life and Total Print
Distance report data strings. Do not use both the p
3
parameters, h and t.
05/30/2007 EPL Programming Guide 14245L-001 Rev. A

---

## Pagina 328

328 2746e Print Odometer
URL
Example • (default)
(cid:41)(cid:38)(cid:34)(cid:37)(cid:1)(cid:1)(cid:86)(cid:84)(cid:66)(cid:72)(cid:70)(cid:1)(cid:30)(cid:1)(cid:57)(cid:57)(cid:57)(cid:13)(cid:57)(cid:57)(cid:57)(cid:13)(cid:57)(cid:57)(cid:57)(cid:1)(cid:3)(cid:1)(cid:1)(cid:1)(cid:60)(cid:57)(cid:57)(cid:57)(cid:1)(cid:69)(cid:66)(cid:90)(cid:84)(cid:62)
(cid:53)(cid:48)(cid:53)(cid:34)(cid:45)(cid:1)(cid:86)(cid:84)(cid:66)(cid:72)(cid:70)(cid:1)(cid:30)(cid:1)(cid:57)(cid:57)(cid:57)(cid:13)(cid:57)(cid:57)(cid:57)(cid:13)(cid:57)(cid:57)(cid:57)(cid:1)(cid:3)(cid:1)(cid:1)(cid:1)(cid:60)(cid:57)(cid:57)(cid:57)(cid:1)(cid:69)(cid:66)(cid:90)(cid:84)(cid:62)
For units with the RTC option installed: The **SERIAL NUMBER MISMATCH ** line
prints when the serial number stored in the RTC does not match the serial number stored on
the printer’s main PCBA. One or both the PCBA or RTC has been changed in this unit and
the odometer data does not accurately represent printer usage.
The last print head record does not include the number of days in service.
14245L-001 Rev. A EPL Programming Guide 05/30/2007

|  |  |
| --- | --- |
|  |  |


---

## Pagina 329

2746e Print Odometer 329
URR
URR
Print Odometer Status Reporting
Description Use this command to print or report (via the serial or USB interface) the status
and settings for the print head life reminder label.
Syntax URRp
1
Parameters This table identifies the parameters for this format. Parameters are optional and
maybe entered and any order.
Parameters Details
p = Report Method Sends information back to the host via the serial port and the
1
(device) last active bi-directional interface.
p = Printer
s = Serial
Default Value: s
Example 1 • Printout Example for oL Command Default Settings
(cid:47)(cid:13)(cid:17)(cid:13)(cid:17)(cid:13)
05/30/2007 EPL Programming Guide 14245L-001 Rev. A

|  |  |
| --- | --- |
|  |  |


---

## Pagina 330

330 2746e Print Odometer
URR
Example 2 • Activated Print Head Reminder (oLy) and the Parameters Customized
(cid:58)(cid:13)(cid:21)(cid:17)(cid:13)(cid:19)(cid:22)(cid:13)(cid:36)(cid:34)(cid:45)(cid:45)(cid:1)(cid:59)(cid:38)(cid:35)(cid:51)(cid:34)(cid:1)(cid:52)(cid:38)(cid:51)(cid:55)(cid:42)(cid:36)(cid:38)(cid:1)(cid:49)(cid:51)(cid:48)(cid:55)(cid:42)(cid:37)(cid:38)(cid:51)
oLy ; Activates Print Head Life Reminder
oL40,25,”CALL ZEBRA SERVICE PROVIDER”
; Sets Threshold: 40 kilometers,
; Frequency: 25 labels,
; Message: “CALL ZEBRA SERVICE PROVIDER”
URRp ; Prints Print Odometer Status label (see example above)
14245L-001 Rev. A EPL Programming Guide 05/30/2007

|  |  |
| --- | --- |
|  |  |


---

## Pagina 331

*[pagina senza testo estraibile — probabilmente figura/scansione]*

---

## Pagina 332

Zebra Technologies Corporation
333 Corporate Woods Parkway
Vernon Hills, Illinois 60061.3109 U.S.A.
Telephone: +1 847 793 2600
Facsimile: +1 847 913 8766
Zebra Technologies Europe Limited
Zebra House
The Valley Centre, Gordon Road
High Wycombe
Buckinghamshire HP13 6EQ, UK
Telephone: +44 (0) 1494 472872
Facsimile: +44 (0) 1494 450103
Zebra Technologies Asia Pacific, LLC
16 New Industrial Road
#05-03 Hudson TechnoCentre
Singapore 536204
Telephone: +65 6858 0722
Facsimile: +65 6885 0838
Part Number: 14245L-001 Rev. A
© 2007 ZIH Corp.
