unit Data;

interface

uses
  Windows,
  SysUtils,
  Classes,
  Graphics,
  Controls,
  Forms,
  Dialogs,
  ExtCtrls,
  Utils;

type

  TSerializedArray = class;

  TSerialized = class(TObject)
  private
    FSerialized: string;
    FDataType: Char;
    FIntegerData: Integer;
    FDoubleData: Double;
    FStringData: string;
    FBooleanData: Boolean;
    FArrayData: TSerializedArray;
  private
    function ParseIntegerData(const Data: string; var Len: Integer): Boolean;
    function ParseDoubleData(const Data: string; var Len: Integer): Boolean;
    function ParseStringData(const Data: string; var Len: Integer): Boolean;
    function ParseBooleanData(const Data: string; var Len: Integer): Boolean;
    function ParseArrayData(const Data: string; var Len: Integer): Boolean;
  public
    constructor Create;
    destructor Destroy; override;
  public
    function ArrayParse(var Serialized: string): Boolean;
    function Parse(const Serialized: string): Boolean;
  public
    property Serialized: string read FSerialized;
    property DataType: Char read FDataType;
  public
    property IntegerData: Integer read FIntegerData;
    property DoubleData: Double read FDoubleData;
    property StringData: string read FStringData;
    property BooleanData: Boolean read FBooleanData;
    property ArrayData: TSerializedArray read FArrayData;
  end;

  TSerializedArray = class(TObject)
  private
    FSerialized: string;
    FItems: Classes.TStrings;
  private
    function GetCount: Integer;
    function GetValue(const Key: string): TSerialized;
  public
    constructor Create;
    destructor Destroy; override;
  public
    procedure Clear;
    function IndexOf(const Key: string): Integer;
    function Parse(const Serialized: string): Boolean;
    function ParseCount(const Serialized: string): Integer;
  public
    property Serialized: string read FSerialized;
    property Count: Integer read GetCount;
    property Items: Classes.TStrings read FItems;
    property Values[const Key: string]: TSerialized read GetValue;
  end;

implementation

uses
  Main;

{ TSerialized }

function TSerialized.ArrayParse(var Serialized: string): Boolean;
var
  S: string;
  L: Integer;
begin
  Result := False;
  FIntegerData := 0;
  FDoubleData := 0.0;
  FStringData := '';
  FBooleanData := False;
  FArrayData.Clear;
  if Length(Serialized) < 3 then
    Exit;
  if Serialized[2] <> ':' then
    Exit;
  S := Copy(Serialized, 3, Length(Serialized) - 2);
  L := -1;
  case Serialized[1] of
    'a':
      if ParseArrayData(Serialized, L) = False then
        Exit;
    'b':
      if ParseBooleanData(S, L) = False then
        Exit;
    'd':
      if ParseDoubleData(S, L) = False then
        Exit;
    'i':
      if ParseIntegerData(S, L) = False then
        Exit;
    's':
      if ParseStringData(S, L) = False then
        Exit;
  else
    Exit;
  end;
  FSerialized := Serialized;
  FDataType := Serialized[1];
  if L >= 0 then
    Delete(Serialized, 1, L);
  Result := True;
end;

constructor TSerialized.Create;
begin
  FArrayData := TSerializedArray.Create;
end;

destructor TSerialized.Destroy;
begin
  FArrayData.Free;
  inherited;
end;

function TSerialized.Parse(const Serialized: string): Boolean;
var
  S: string;
begin
  S := Serialized;
  Result := ArrayParse(S);
end;

function TSerialized.ParseArrayData(const Data: string; var Len: Integer): Boolean;
begin
  Len := FArrayData.ParseCount(Data);
  Result := Len <> -1;
end;

function TSerialized.ParseBooleanData(const Data: string; var Len: Integer): Boolean;
begin
  Len := 1;
  Result := False;
  if Data = '1' then
  begin
    FBooleanData := True;
    Result := True;
  end
  else
    if Data = '0' then
    begin
      FBooleanData := False;
      Result := True;
    end;
end;

function TSerialized.ParseDoubleData(const Data: string; var Len: Integer): Boolean;
var
  S: string;
  i: Integer;
begin
  S := Data;
  i := Pos(';', S);
  if i > 0 then
    S := Copy(S, 1, i - 1);
  Len := Length(S);
  try
    FDoubleData := SysUtils.StrToFloat(S);
    Result := True;
  except
    FDoubleData := 0.0;
    Result := False;
  end;
end;

function TSerialized.ParseIntegerData(const Data: string; var Len: Integer): Boolean;
var
  S: string;
  i: Integer;
begin
  S := Data;
  i := Pos(';', S);
  if i > 0 then
    S := Copy(S, 1, i - 1);
  Len := Length(S);
  try
    FIntegerData := SysUtils.StrToInt(S);
    Result := True;
  except
    FIntegerData := 0;
    Result := False;
  end;
end;

function TSerialized.ParseStringData(const Data: string; var Len: Integer): Boolean;
begin
  Result := ExtractSerialzedString(Data, FStringData);
  Len := Length(SysUtils.IntToStr(Length(FStringData))) + 3 + Length(FStringData);
end;

{ TSerializedArray }

procedure TSerializedArray.Clear;
var
  i: Integer;
begin
  for i := 0 to Count - 1 do
    FItems.Objects[i].Free;
  FItems.Clear;
end;

constructor TSerializedArray.Create;
begin
  FItems := Classes.TStringList.Create;
end;

destructor TSerializedArray.Destroy;
begin
  Clear;
  FItems.Free;
  inherited;
end;

function TSerializedArray.GetCount: Integer;
begin
  Result := FItems.Count;
end;

function TSerializedArray.GetValue(const Key: string): TSerialized;
var
  i: Integer;
begin
  i := IndexOf(Key);
  if i >= 0 then
    Result := TSerialized(FItems.Objects[i])
  else
    Result := nil;
end;

function TSerializedArray.IndexOf(const Key: string): Integer;
var
  i: Integer;
begin
  for i := 0 to Count - 1 do
    if FItems[i] = Key then
    begin
      Result := i;
      Exit;
    end;
  Result := -1;
end;

function TSerializedArray.Parse(const Serialized: string): Boolean;
begin
  Result := ParseCount(Serialized) <> -1;
end;

function TSerializedArray.ParseCount(const Serialized: string): Integer;
var
  S: string;
  n: Integer;
  i: Integer;
  L: Integer;
  Children: TList;
  Child: TSerialized;
begin
  FSerialized := Serialized;
  Result := -1;
  S := Serialized;
  Delete(S, 1, 2);
  i := Pos(':', S);
  if i <= 0 then
    Exit;
  try
    n := SysUtils.StrToInt(Copy(S, 1, i - 1)); // number of elements in the array
  except
    Exit;
  end;
  Delete(S, 1, i);
  if Length(S) < 2 then
    Exit;
  if (S[1] <> '{') or (S[Length(S)] <> '}') then
    Exit;
  Delete(S, 1, 1);
  Delete(S, Length(S), 1);
  Children := TList.Create;
  try
    for i := 1 to n * 2 do
    begin
      Child := TSerialized.Create;
      L := 0;
      if Child.ParseArrayData(S, L) = False then
      begin
        Child.Free;
        Exit;
      end
      else
      begin
        Children.Add(Child);
        if S[1] = ';' then
          Delete(S, 1, 1)
        else
          Exit;
      end;
    end;
    for i := 1 to n do
    begin
      case TSerialized(Children[i * 2 - 1]).DataType of
        's': FItems.AddObject(TSerialized(Children[i * 2 - 1]).StringData, TSerialized(Children[i * 2]));
        'i': FItems.AddObject(SysUtils.IntToStr(TSerialized(Children[i * 2 - 1]).IntegerData), TSerialized(Children[i * 2]));
      else
        Exit;
      end;
    end;
    for i := 1 to n do
      TSerialized(Children[i * 2 - 1]).Free;
    Result := Length(Serialized) - Length(S);
  finally
    if Result = -1 then
      for i := 0 to Children.Count - 1 do
        TSerialized(Children[i]).Free;
    Children.Free;
  end;
end;

end.