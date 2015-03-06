require 'emoji/extractor'
dir = File.dirname(File.realpath(__FILE__))
Emoji::Extractor.new(160, "#{dir}/../../img-apple-160").extract!
