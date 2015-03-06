require 'emoji/extractor'
dir = File.dirname(File.realpath(__FILE__))
Emoji::Extractor.new(128, "#{dir}/../../apple_128").extract!
