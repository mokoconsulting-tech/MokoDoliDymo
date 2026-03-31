# DYMO Label File Format

This document describes the DYMO Desktop Label XML format (`.dymo` / `.label` files) used by DYMO Connect and DYMO Label software. MokoDoliDymo can import these files as label template starting points.

## File Structure

DYMO label files are XML with the root element `<DesktopLabel Version="1">`.

```xml
<?xml version="1.0" encoding="utf-8"?>
<DesktopLabel Version="1">
  <DYMOLabel Version="4">
    <!-- Label metadata and layout -->
  </DYMOLabel>
  <LabelApplication>Blank</LabelApplication>
  <DataTable>
    <Columns></Columns>
    <Rows></Rows>
  </DataTable>
</DesktopLabel>
```

## Key Elements

### DYMOLabel

The main label definition container.

| Element | Description |
|---------|-------------|
| `Description` | Human-readable label description |
| `Orientation` | `Landscape` or `Portrait` |
| `LabelName` | DYMO label stock name (e.g. `Address30251`, `Shipping30256`) |
| `DYMORect` | Label printable area rectangle |
| `DynamicLayoutManager > LabelObjects` | Container for all label elements |

### DYMORect / ObjectLayout — Coordinates

All coordinates and sizes are in **inches**. Each object has an `ObjectLayout` block:

```xml
<ObjectLayout>
  <DYMOPoint>
    <X>0.9475</X>      <!-- X position in inches from left -->
    <Y>0.2958333</Y>   <!-- Y position in inches from top -->
  </DYMOPoint>
  <Size>
    <Width>1.605</Width>    <!-- Width in inches -->
    <Height>0.4983333</Height>  <!-- Height in inches -->
  </Size>
</ObjectLayout>
```

**Conversion**: 1 inch = 25.4 mm

The label's printable area is defined by `DYMORect` with the same `DYMOPoint` + `Size` structure.

### Label Objects

Objects live inside `DynamicLayoutManager > LabelObjects`. Supported types:

#### TextObject

```xml
<TextObject>
  <Name>TextObject0</Name>
  <HorizontalAlignment>Center</HorizontalAlignment>  <!-- Left, Center, Right -->
  <VerticalAlignment>Middle</VerticalAlignment>        <!-- Top, Middle, Bottom -->
  <FitMode>AlwaysFit</FitMode>                         <!-- None, AlwaysFit, ShrinkToFit -->
  <FormattedText>
    <LineTextSpan>
      <TextSpan>
        <Text>Hello World</Text>
        <FontInfo>
          <FontName>Segoe UI</FontName>
          <FontSize>26.9</FontSize>
          <IsBold>False</IsBold>
          <IsItalic>False</IsItalic>
          <IsUnderline>False</IsUnderline>
        </FontInfo>
      </TextSpan>
    </LineTextSpan>
  </FormattedText>
  <ObjectLayout>...</ObjectLayout>
</TextObject>
```

#### BarcodeObject

```xml
<BarcodeObject>
  <Name>BarcodeObject0</Name>
  <BarcodeFormat>Code128Auto</BarcodeFormat>  <!-- Code128Auto, QRCode, EAN13, etc. -->
  <Text>123456789</Text>
  <TextPosition>Bottom</TextPosition>          <!-- None, Top, Bottom -->
  <ObjectLayout>...</ObjectLayout>
</BarcodeObject>
```

Common barcode formats: `Code128Auto`, `QRCode`, `EAN13`, `EAN8`, `UPCA`, `Code39`, `ITF14`, `PDF417`, `DataMatrix`

#### ImageObject

```xml
<ImageObject>
  <Name>ImageObject0</Name>
  <ScaleMode>Uniform</ScaleMode>  <!-- None, Uniform, Fill -->
  <Image><!-- Base64-encoded image data --></Image>
  <ObjectLayout>...</ObjectLayout>
</ImageObject>
```

#### ShapeObject

```xml
<ShapeObject>
  <Name>ShapeObject0</Name>
  <ShapeType>Rectangle</ShapeType>  <!-- Rectangle, Ellipse, Line, VerticalLine -->
  <ObjectLayout>...</ObjectLayout>
</ShapeObject>
```

### Common Properties

All label objects share these:

| Property | Values | Description |
|----------|--------|-------------|
| `Rotation` | `Rotation0`, `Rotation90`, `Rotation180`, `Rotation270` | Element rotation |
| `Brushes` | Complex | Fill, border, stroke, and background colors |
| `Margin` | `DYMOThickness Left/Top/Right/Bottom` | Inner padding (inches) |
| `BorderStyle` | `SolidLine`, `None` | Border drawing style |
| `OutlineThickness` | Float | Outline stroke width |

### Label Stock Names

The `LabelName` field identifies the DYMO label roll. Common values:

| LabelName | DYMO Part# | Size (inches) | Size (mm) |
|-----------|-----------|---------------|-----------|
| `Address30251` | 30251 | 1-1/8 x 3-1/2 | 28.6 x 88.9 |
| `Address30252` | 30252 | 1-1/8 x 3-1/2 | 28.6 x 88.9 |
| `Shipping30256` | 30256 | 2-5/16 x 4 | 58.7 x 101.6 |
| `ReturnAddress30330` | 30330 | 3/4 x 2 | 19.1 x 50.8 |
| `MultiPurpose30334` | 30334 | 1 x 2-1/8 | 25.4 x 57.2 |
| `FileFolder30327` | 30327 | 9/16 x 3-7/16 | 14.3 x 87.3 |
| `NameBadge30857` | 30857 | 2-1/4 x 4 | 57.2 x 101.6 |

## MokoDoliDymo Import Behavior

When importing a `.dymo` or `.label` file:

1. The XML is parsed to extract the label dimensions from `DYMORect`
2. Each `LabelObject` is converted to a MokoDoliDymo layout element
3. Coordinates are converted from inches to millimeters (multiply by 25.4)
4. Font sizes and text properties are preserved where possible
5. Barcode types are mapped to Dolibarr-compatible formats
6. Image data is extracted and stored separately
7. The resulting JSON layout can be further edited in the visual designer

## References

- [DYMO Developer SDK](https://developers.dymo.com/)
- [DYMO Connect JavaScript Framework](https://developers.dymo.com/tag/javascript/)
- [DYMO Label Framework Technical Reference](https://developers.dymo.com/2010/06/02/dymo-label-framework-javascript-library-samples-and-docs/)
