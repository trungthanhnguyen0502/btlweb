import cv2 as cv
import numpy as np
from math import *

UNIT_SIZE = 7
selectedPiece = -1
selectedX = -1
selectedY = -1

# Read from file
file = open("input.txt", "r")
inputs = []
for line in file:
    nums = [int(x) for x in line.split()]
    inputs.append(nums)
# print(inputs)

# Save pieces coordinate
number_of_pieces = inputs[0][0]
pieces = []
frame = []
for i in range (1, number_of_pieces + 1):
    l = len(inputs[i])
    set_of_points = []
    for j in range (2, l - 1, 2):
        x = inputs[i][j] * UNIT_SIZE
        y = inputs[i][j + 1] * UNIT_SIZE
        set_of_points.append([x, y])
    pieces.append(np.array(set_of_points))
# print(pieces)

# Save frame coordinate
l = len(inputs[number_of_pieces + 1])
for i in range (2, l - 1, 2):
    x, y = (inputs[number_of_pieces + 1][i] * UNIT_SIZE, inputs[number_of_pieces + 1][i + 1] * UNIT_SIZE)
    frame.append([x, y])
frame = np.array(frame)
# print(frame)

# Mouse event handling
def mouseHandle(event, x, y, flags, params):
    global selectedPiece
    global selectedX
    global selectedY
    if event == cv.EVENT_LBUTTONDOWN:
        selectedX = x
        selectedY = y
        for i in range (0, number_of_pieces):
            if cv.pointPolygonTest(pieces[i], (x, y), False) > 0:
                selectedPiece = i + 1
                break
            if i == 14:
                selectedPiece = -1
        # print(selectedPiece)

    elif event == cv.EVENT_MOUSEMOVE and flags == cv.EVENT_FLAG_LBUTTON:
        if selectedPiece > 0:
            movesX = int((x - selectedX) / UNIT_SIZE)
            movesY = int((y - selectedY) / UNIT_SIZE)
            if (abs(movesX) > 0):
                selectedX = x
            if (abs(movesY) > 0):
                selectedY = y
            # print(selectedX, selectedY, x, y, movesX, movesY)
            movePiece(selectedPiece, movesX, movesY)
            draw_pieces()
            cv.imshow("draw", img)

    elif event == cv.EVENT_LBUTTONUP:
        selectedX = -1
        selectedY = -1

# rotate pieces
def rotatePiece(selectedPiece):
    print(selectedPiece)
    a = pi/2
    tmp_piece = []
    piece = pieces[selectedPiece - 1]
    l = len(piece)
    x0 = piece[0][0]
    y0 = piece[0][1]
    tmp_piece.append([x0, y0])
    for i in range (1, l):
        x1 = piece[i][0]
        y1 = piece[i][1]
        x2 = ((x1 - x0) * cos(a)) - ((y1 - y0) * sin(a)) + x0
        y2 = ((x1 - x0) * sin(a)) + ((y1 - y0) * cos(a)) + y0
        tmp_piece.append([int(round(x2, 0)), int(round(y2, 0))])
    tmp_piece = np.array(tmp_piece)
    pieces.pop(selectedPiece - 1)
    pieces.insert(selectedPiece - 1, tmp_piece)

# flip piece
def flipPiece(selectedPiece):
    print(selectedPiece)
    tmp_piece = []
    piece = pieces[selectedPiece - 1]
    l = len(piece)
    x0 = piece[0][0]
    y0 = piece[0][1]
    tmp_piece.append([x0, y0])
    for i in range(1, l):
        x1 = piece[i][0]
        y1 = piece[i][1]
        x2 = 2 * x0 - x1
        y2 = y1
        tmp_piece.append([int(round(x2, 0)), int(round(y2, 0))])
    tmp_piece = np.array(tmp_piece)
    pieces.pop(selectedPiece - 1)
    pieces.insert(selectedPiece - 1, tmp_piece)

# move piece
def movePiece(selectedPiece, movesX, movesY):
    # print(selectedPiece, movesX, movesY)
    tmp_piece = []
    piece = pieces[selectedPiece - 1]
    # print(pieces[selectedPiece - 1])
    l = len(piece)
    for i in range(0, l):
        x1 = piece[i][0]
        y1 = piece[i][1]
        x2 = x1 + movesX * UNIT_SIZE
        y2 = y1 + movesY * UNIT_SIZE
        tmp_piece.append([int(round(x2, 0)), int(round(y2, 0))])
    tmp_piece = np.array(tmp_piece)
    pieces.pop(selectedPiece - 1)
    pieces.insert(selectedPiece - 1, tmp_piece)

# Draw pieces
def draw_pieces():
    global img
    img = np.zeros((800, 1200, 3), np.uint8)
    cv.drawContours(img, [frame], 0, (255, 0, 0), 1)
    cv.drawContours(img, pieces, -1, (0, 0, 255), 2)
    font = cv.FONT_HERSHEY_SIMPLEX
    for i in range (number_of_pieces):
        piece = pieces[i]
        x = piece[0][0] + 5
        y = piece[0][1] + 5
        cv.putText(img, str(i + 1), (x, y), font, 0.5, (255, 255, 255), 1, cv.LINE_AA)

# Print result to file
def printResult():
    file = open("output.txt", "w")
    file.write(str(number_of_pieces) + "\n")
    # print(number_of_pieces)
    for i in range (number_of_pieces):
        piece = pieces[i]
        l = len(piece)
        line = str(i + 1) + " " + str(l) + " "
        # print(i + 1, l, end=" ")

        for j in range (l):
            line += str(piece[j][0] // UNIT_SIZE) + " " + str(piece[j][1] // UNIT_SIZE) + " "
            # print(piece[j][0], piece[j][1], end= " ")
        file.write(line + "\n")
        # print()
    l = len(frame)

    line = str(number_of_pieces + 1) + " " + str(l) + " "
    # print(number_of_pieces + 1, l, end= " ")
    for i in range (l):
        line += str(frame[i][0] // UNIT_SIZE) + " " + str(frame[i][1] // UNIT_SIZE) + " "
        # print(frame[i][0] // UNIT_SIZE, frame[i][1] // UNIT_SIZE, end=" ")
    file.write(line)
    # print()
    file.close()

# Main
img = np.zeros((800, 1200, 3), np.uint8)
while 1:
    draw_pieces()
    cv.imshow("draw", img)
    cv.setMouseCallback("draw", mouseHandle)
    k = cv.waitKey()
    if k == ord('r') and selectedPiece != -1:
        rotatePiece(selectedPiece)
    elif k == ord('f') and selectedPiece != -1:
        flipPiece(selectedPiece)
    elif k == ord('a') and selectedPiece != -1:
        movePiece(selectedPiece, -1, 0)
    elif k == ord('s') and selectedPiece != -1:
        movePiece(selectedPiece, 0, 1)
    elif k == ord('w') and selectedPiece != -1:
        movePiece(selectedPiece, 0, -1)
    elif k == ord('d') and selectedPiece != -1:
        movePiece(selectedPiece, 1, 0)
    elif k == ord('p'):
        printResult()