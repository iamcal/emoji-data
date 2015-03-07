require 'emoji'
require 'ttfunk'
require 'pathname'

module Emoji
  class Extractor

    attr_reader :ttf, :size, :images_path

    def initialize(size, ttf_file, images_dir)
      @size = size
      @images_path = Pathname.new(images_dir)
      @ttf = TTFunk::File.open(Pathname.new(ttf_file))
    end

    def extract!
      ttf.maximum_profile.num_glyphs.times do |glyph_id|
        bitmap = extract_bitmap(glyph_id)
        next if bitmap.nil?

        ttf_name = ttf.postscript.glyph_for(glyph_id)
        hexes = ttf_name.split('_').map { |n| n.gsub(/^u/, '').downcase }

        if emoji_char_codes.include?(hexes.first.to_i(16))
          filename = "#{hexes.join('-')}.#{bitmap.type}"
          File.write(images_path.join(filename), bitmap.data.read)
        end
      end
    end

    private
      def extract_bitmap(glyph_id)
        bitmaps = ttf.sbix.all_bitmap_data_for(glyph_id)
        bitmaps.detect { |b| b.ppem == size }
      end

      def emoji_char_codes
        @emoji_char_codes ||= Emoji.all.reject(&:custom?).map { |e| e.raw.codepoints[0] }
      end
  end
end
