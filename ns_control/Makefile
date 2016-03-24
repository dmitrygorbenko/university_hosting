
CC = g++
CCFLAGS = -g -Wall
TARGET = ns_control

FILES = dialog dns domains login ns_control serial str tools zone_info zones
OBJS = $(addsuffix .o, $(FILES))

all: $(TARGET) link-all

$(TARGET): $(OBJS) 

%.o: %.cpp %.h define.h
	$(CC) $(CCFLAGS) -c $< -o $@

link-all:
	$(CC) -o $(TARGET) $(OBJS)

clean:
	rm -rf $(OBJS) $(TARGET)
