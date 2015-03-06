require 'emoji/extractor'
dir = File.dirname(File.realpath(__FILE__))
Emoji::Extractor.new(160, "#{dir}/NotoColorEmoji.ttf", "#{dir}/../../img-android").extract!
