<?php

declare(strict_types=1);

namespace Anydoc {
    final readonly class Anchor extends Inline
    {
        public string $anchor;
    }

    final readonly class AnchorLink extends LinkTarget
    {
        public string $value;
    }

    final readonly class Asset
    {
        public string $data;

        public int $id;

        public string $mediaType;

        public string $originPart;
    }

    final readonly class AssetImage extends ImageSource
    {
        public int $assetId;
    }

    abstract readonly class Block {}

    final readonly class BlockList extends Block
    {
        public DocumentList $list;
    }

    final readonly class BlockQuote extends Block
    {
        /** @var list<Block> */
        public array $blocks;
    }

    final readonly class BlockTable extends Block
    {
        public Table $table;
    }

    final readonly class Cell
    {
        /** @var list<Block> */
        public array $blocks;

        public int $colSpan;

        public int $rowSpan;
    }

    abstract readonly class CellSlot {}

    final readonly class Checkbox extends Inline
    {
        public bool $checked;
    }

    final readonly class CodeBlock extends Block
    {
        public ?string $lang;

        public string $text;
    }

    final readonly class CoveredCell extends CellSlot
    {
        public int $originCol;

        public int $originRow;
    }

    final readonly class Document
    {
        /** @var list<Asset> */
        public array $assets;

        /** @var list<Block> */
        public array $blocks;

        /** @var list<Note> */
        public array $notes;
    }

    final readonly class DocumentList
    {
        /** @var list<ListItem> */
        public array $items;

        public string $marker;

        public int $start;
    }

    final readonly class ExternalImage extends ImageSource
    {
        public string $url;
    }

    final readonly class ExternalLink extends LinkTarget
    {
        public string $value;
    }

    final readonly class Heading extends Block
    {
        public ?string $anchor;

        /** @var list<Inline> */
        public array $content;

        public int $level;
    }

    final readonly class Image extends Inline
    {
        public string $alt;

        public ImageSource $source;
    }

    abstract readonly class ImageSource {}

    abstract readonly class Inline {}

    final readonly class LineBreak extends Inline {}

    final readonly class Link extends Inline
    {
        /** @var list<Inline> */
        public array $content;

        public LinkTarget $target;
    }

    abstract readonly class LinkTarget {}

    final readonly class ListItem
    {
        /** @var list<Block> */
        public array $blocks;

        public ?string $markerLabel;
    }

    final readonly class MathBlock extends Block
    {
        public string $text;
    }

    final readonly class MathInline extends Inline
    {
        public string $text;
    }

    final readonly class Note
    {
        /** @var list<Block> */
        public array $blocks;

        public string $id;

        public string $kind;
    }

    final readonly class NoteReference extends Inline
    {
        public string $noteId;
    }

    final readonly class OriginCell extends CellSlot
    {
        public Cell $cell;
    }

    final readonly class Paragraph extends Block
    {
        /** @var list<Inline> */
        public array $content;
    }

    final readonly class RelativeLink extends LinkTarget
    {
        public string $value;
    }

    final readonly class Rule extends Block {}

    final readonly class Style
    {
        public bool $bold;

        public bool $code;

        public bool $italic;

        public bool $strike;
    }

    final readonly class Table
    {
        /** @var list<list<CellSlot>> */
        public array $grid;

        public int $headerRows;

        public string $kind;
    }

    final readonly class Text extends Inline
    {
        public Style $style;

        public string $text;
    }

    final readonly class UnavailableImage extends ImageSource {}
}

namespace Anydoc\Exception {
    class ConvertException extends \Exception {}

    final class EncryptedException extends ConvertException
    {
        public const ERROR_CODE = 'encrypted';
    }

    final class IoException extends ConvertException
    {
        public const ERROR_CODE = 'io';
    }

    final class MalformedException extends ConvertException
    {
        public const ERROR_CODE = 'malformed';

        public string $detail;

        public ?string $part;
    }

    final class MissingPartException extends ConvertException
    {
        public const ERROR_CODE = 'missingPart';

        public string $part;
    }

    final class PanicException extends \Exception {}

    final class ResourceLimitException extends ConvertException
    {
        public const ERROR_CODE = 'resourceLimit';

        public string $detail;

        public string $limit;
    }

    final class UnsupportedException extends ConvertException
    {
        public const ERROR_CODE = 'unsupported';

        public string $detail;
    }
}

namespace {
    function anydoc_format_from_bytes(string $bytes): ?string {}

    function anydoc_format_from_extension(string $extension): ?string {}

    function anydoc_format_from_path(string $path): ?string {}

    function anydoc_to_document(string $bytes, ?string $format = null): \Anydoc\Document {}

    function anydoc_to_markdown(string $path): string {}

    function anydoc_to_markdown_bytes(string $bytes, ?string $format = null): string {}
}
