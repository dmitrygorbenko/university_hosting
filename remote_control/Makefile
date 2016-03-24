
CC = g++
CCFLAGS = -g -Wall
TARGET = remote_control

FILES = apache backup dialog dirs login mail ns protect remote_control service pwd stat str tools
OBJS = $(addsuffix .o, $(FILES))

all: $(TARGET) link-all

$(TARGET): $(OBJS) 

%.o: %.cpp %.h define.h
	$(CC) $(CCFLAGS) -c $< -o $@

link-all:
	$(CC) -o $(TARGET) $(OBJS)

clean:
	rm -rf $(OBJS) $(TARGET)
