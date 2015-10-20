unit bot_main;

interface

uses
  Windows,
  SysUtils,
  Classes,
  Graphics,
  Controls,
  Forms,
  Dialogs,
  StdCtrls,
  DateUtils,
  ComCtrls,
  Messages,
  Grids,
  ExtCtrls,
  Menus,
  bot_data,
  bot_test;

type

  TFormMain = class(TForm)
    MemoData: TMemo;
    ButtonRunTests: TButton;
    procedure FormCreate(Sender: TObject);
    procedure FormDestroy(Sender: TObject);
    procedure ButtonRunTestsClick(Sender: TObject);
  private
    FServers: bot_data.TBotServerArray;
  private
    procedure ReceiveHandler(const S: string);
  end;

var
  FormMain: TFormMain;

implementation

{$R *.dfm}

{ TFormMain }

procedure TFormMain.FormCreate(Sender: TObject);
begin
  FServers := bot_data.TBotServerArray.Create(ReceiveHandler);
  FServers.Add.Connect;
end;

procedure TFormMain.FormDestroy(Sender: TObject);
begin
  FServers.Free;
end;

procedure TFormMain.ReceiveHandler(const S: string);
begin
  MemoData.Lines.Add(S);
end;

procedure TFormMain.ButtonRunTestsClick(Sender: TObject);
begin
  bot_test.RunTests;
end;

end.