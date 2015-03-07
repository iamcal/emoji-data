# stolen largely from http://www.ruby-forum.com/topic/140784

require 'stringio'
require 'fileutils'

dir = File.dirname(File.realpath(__FILE__))
ttf_file = "#{dir}/NotoColorEmoji_lollipop.ttf"

def extract_chunk(input, output)
  lenword = input.read(4)
  length = lenword.unpack('N')[0]
  type = input.read(4)
  data = length>0 ? input.read(length) : ""
  crc = input.read(4)
  return nil if length<0 || !(('A'..'z')===type[0,1])
  #return nil if validate_crc(type+data, crc)
  output.write lenword
  output.write type
  output.write data
  output.write crc
  return [type, data]
end

def extract_png(input)
  buf = StringIO.new
  hdr = input.read(8)
  raise "Not a PNG File" if hdr[0,4]!= "\211PNG"
  raise "file not in binary mode" if hdr[4,4]!="\r\n\032\n"
  buf.write(hdr)

  height, width = 0, 0

  loop do
    chunk_type, chunk_data = extract_chunk(input,buf)
    height, width = chunk_data.unpack('NN') if chunk_type == 'IHDR'
    break if  chunk_type.nil? || chunk_type == 'IEND'
  end

  FileUtils.mkdir_p(dir = "images")
  if @prev != dir
    @n = 0
    @prev = dir
  end

  buf.rewind
  ofp = File.new("#{dir}/#{@n+=1}.png","wb")
  ofp.write buf.read
  ofp.close
end

ttf = File.new(ttf_file,"rb")
ttf_data = ttf.read

pos = 0
while m = /\211PNG/.match(ttf_data[pos..-1])
  raise "no PNG found" if !m
  pos += m.begin(0) + 1
  ttf.seek(pos-1)

  extract_png(ttf)
end
