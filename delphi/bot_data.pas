unit bot_data;

interface

uses
  Windows,
  SysUtils,
  Classes,
  Graphics,
  Controls,
  Forms,
  Sockets,
  ScktComp,
  Dialogs,
  ExtCtrls,
  StrUtils;

type

  TBotClientThread = class;
  TBotServer = class;
  TBotServerArray = class;
  TBotMessage = class;
  TBotMessageArray = class;
  TBotChannel = class;
  TBotChannelArray = class;
  TBotUser = class;
  TBotUserArray = class;

  { TBotClientThread }

  TBotClientThread = class(TThread)
  private
    FClient: Sockets.TTcpClient;
    FBuffer: string;
    FHandler: Classes.TGetStrProc;
  private
    procedure ClientError(Sender: TObject; SocketError: Integer);
    procedure ClientSend(Sender: TObject; Buf: PAnsiChar; var DataLen: Integer);
  public
    constructor Create(CreateSuspended: Boolean);
    procedure Update;
    procedure Send(const Msg: string);
    procedure Execute; override;
  public
    property Handler: Classes.TGetStrProc read FHandler write FHandler;
  end;

  { TBotServer }

  TBotServer = class(TObject)
  private
    FHandler: Classes.TGetStrProc;
    FThread: TBotClientThread;
  private
    procedure ThreadHandler(const S: string);
  public
    constructor Create(const Handler: Classes.TGetStrProc);
    destructor Destroy; override;
  public
    procedure Connect;
  public
    property Handler: TGetStrProc read FHandler write FHandler;
  end;

  { TBotServerArray }

  TBotServerArray = class(TObject)
  private
    FGlobalHandler: Classes.TGetStrProc;
    FServers: Classes.TList;
  private
    function GetCount: Integer;
    function GetServer(const Index: Integer): TBotServer;
  public
    constructor Create(const GlobalHandler: Classes.TGetStrProc);
    destructor Destroy; override;
  public
    function Add: TBotServer;
  public
    property Count: Integer read GetCount;
    property Servers[const Index: Integer]: TBotServer read GetServer;
  end;

  { TBotMessage }

  TBotMessage = class(TObject)
  private
    FCommand: string;
    FData: string;
    FDestination: string;
    FHostname: string;
    FNick: string;
    FParams: string;
    FPrefix: string;
    FServer: string;
    FTimeStamp: TDateTime;    FTrailing: string;
    FUser: string;
    FValid: Boolean;
  public
    constructor Create(const Data: string);
  public
    property Command: string read FCommand;
    property Data: string read FData;
    property Destination: string read FDestination;
    property Hostname: string read FHostname;
    property Nick: string read FNick;
    property Params: string read FParams;
    property Prefix: string read FPrefix;
    property Server: string read FServer;
    property TimeStamp: TDateTime read FTimeStamp;    property Trailing: string read FTrailing;    property User: string read FUser;    property Valid: Boolean read FValid;  end;

  { TBotMessageArray }

  TBotMessageArray = class(TObject)
  private
    FMessages: TList;
  private
    function GetCount: Integer;
    function GetMessage(const Index: Integer): TBotMessage;
  public
    constructor Create;
    destructor Destroy; override;
  public
    procedure Add(const Data: string);
    procedure Clear;
  public
    property Count: Integer read GetCount;
    property Messages[const Index: Integer]: TBotMessage read GetMessage; default;
  end;

  { TBotChannel }

  TBotChannel = class(TObject)
  private

  public
    constructor Create;
    destructor Destroy; override;
  public

  end;

  { TBotChannelArray }

  TBotChannelArray = class(TObject)
  private

  public
    constructor Create;
    destructor Destroy; override;
  public

  end;

  { TBotUser }

  TBotUser = class(TObject)
  private

  public
    constructor Create;
  public

  end;

  { TBotUserArray }

  TBotUserArray = class(TObject)
  private

  public
    constructor Create;
    destructor Destroy; override;
  public

  end;

implementation

{ TBotClientThread }

constructor TBotClientThread.Create(CreateSuspended: Boolean);
begin
  inherited;
  FreeOnTerminate := True;
end;

procedure TBotClientThread.Execute;
var
  Buf: Char;
const
  TERMINATOR: string = #13#10;
begin
  try
    FClient := TTcpClient.Create(nil);
    FClient.OnError := ClientError;
    FClient.OnSend := ClientSend;
    try
      FClient.RemoteHost := FClient.LookupHostAddr('irc.sylnt.us');
      FClient.RemotePort := '6667';
      if FClient.Connect = False then
      begin
        FBuffer := '<< CONNECTION ERROR >>';
        Synchronize(Update);
        Exit;
      end;
      FBuffer := '<< CONNECTED >>';
      Synchronize(Update);

      Send('NICK delphi_exec_test');
      Send('USER delphi_exec_test hostname servername :delphi_exec_test');

      FBuffer := '';
      while (Application.Terminated = False) and (Self.Terminated = False) and (FClient.Connected = True) do
      begin
        Buf := #0;
        FClient.ReceiveBuf(Buf, 1);
        if Buf <> #0 then
        begin
          FBuffer := FBuffer + Buf;
          if Copy(FBuffer, Length(FBuffer) - Length(TERMINATOR) + 1, Length(TERMINATOR)) = TERMINATOR then
          begin
            FBuffer := Copy(FBuffer, 1, Length(FBuffer) - Length(TERMINATOR));
            Synchronize(Update);
            FBuffer := '';
          end;
        end
        else
        begin
          if FBuffer <> '' then
          begin
            Synchronize(Update);
            FBuffer := '';
          end;
        end;
      end;
      FBuffer := '<< DISCONNECTED >>';
      Synchronize(Update);
    finally
      FClient.Free;
    end;
  except
    FBuffer := '<< EXCEPTION ERROR >>';
    Synchronize(Update);
  end;
end;

procedure TBotClientThread.Send(const Msg: string);
begin
  if Assigned(FClient) then
    if FClient.Connected then
      FClient.Sendln(Msg);
end;

procedure TBotClientThread.Update;
begin
  if Assigned(FHandler) then
    FHandler(FBuffer);
end;

procedure TBotClientThread.ClientError(Sender: TObject; SocketError: Integer);
begin

end;

procedure TBotClientThread.ClientSend(Sender: TObject; Buf: PAnsiChar; var DataLen: Integer);
begin

end;

{ TBotServer }

procedure TBotServer.Connect;
begin
  FThread.Resume;
end;

constructor TBotServer.Create(const Handler: Classes.TGetStrProc);
begin
  FHandler := Handler;
  FThread := TBotClientThread.Create(True);
  FThread.Handler := ThreadHandler;
end;

destructor TBotServer.Destroy;
begin

  inherited;
end;

procedure TBotServer.ThreadHandler(const S: string);
begin
  if Assigned(FHandler) then
    FHandler(S);
end;

{ TBotServerArray }

function TBotServerArray.Add: TBotServer;
begin
  Result := TBotServer.Create(FGlobalHandler);
  FServers.Add(Result);
end;

constructor TBotServerArray.Create(const GlobalHandler: Classes.TGetStrProc);
begin
  FGlobalHandler := GlobalHandler;
  FServers := Classes.TList.Create;
end;

destructor TBotServerArray.Destroy;
var
  i: Integer;
begin
  for i := 0 to Count - 1 do
    Servers[i].Free;
  FServers.Free;
  inherited;
end;

function TBotServerArray.GetCount: Integer;
begin
  Result := FServers.Count;
end;

function TBotServerArray.GetServer(const Index: Integer): TBotServer;
begin
  if (Index >= 0) and (Index < Count) then
    Result := FServers[Index]
  else
    Result := nil;
end;

{ TBotMessage }

constructor TBotMessage.Create(const Data: string);
var
  S: string;
  sub: string;
  i: Integer;
begin
  FValid := False;
  FTimeStamp := Now;
  FData := Data;
  S := Data;
  // :<prefix> <command> <params> :<trailing>
  // the only required part of the message is the command
  // if there is no prefix, then the source of the message is the server for the current connection (such as for PING)
  if Copy(Data, 1, 1) = ':' then
  begin
    i := Pos(' ', S);
    if i > 0 then
    begin
      FPrefix := Copy(S, 2, i - 2);
      S := Copy(S, i + 1, Length(S) - i);
    end;
  end;
  i := Pos(' :', S);
  if i > 0 then
  begin
    FTrailing := Copy(S, i + 2, Length(S) - i - 1);
    S := Copy(S, 1, i - 1);
  end;
  i := Pos(' ', S);
  if i > 0 then
  begin
    // params found
    FParams := Copy(S, i + 1, Length(S) - i);
    S := Copy(S, 1, i - 1);
  end;
  FCommand := S;
  if FCommand = '' then
    Exit;
  FValid := True;
  if FPrefix <> '' then
  begin
    // prefix format: nick!user@hostname
    i := Pos('!', FPrefix);
    if i > 0 then
    begin
      FNick := Copy(FPrefix, 1, i - 1);
      sub := Copy(FPrefix, i + 1, Length(FPrefix) - i);
      i := Pos('@', sub);
      if i > 0 then
      begin
        FUser := Copy(sub, 1, i - 1);
        FHostname := Copy(sub, i + 1, Length(sub) - i);
      end;
    end
    else
      FNick := FPrefix;
  end;
  i := Pos(' ', FParams);
  if i <= 0 then
    FDestination := FParams;
end;

{ TBotMessageArray }

procedure TBotMessageArray.Add(const Data: string);
var
  NewMessage: TBotMessage;
begin
  NewMessage := TBotMessage.Create(Data);
  FMessages.Add(NewMessage);
end;

procedure TBotMessageArray.Clear;
var
  i: Integer;
begin
  for i := 0 to Count - 1 do
    Messages[i].Free;
  FMessages.Clear;
end;

constructor TBotMessageArray.Create;
begin
  FMessages := TList.Create;
end;

destructor TBotMessageArray.Destroy;
begin
  Clear;
  FMessages.Free;
  inherited;
end;

function TBotMessageArray.GetCount: Integer;
begin
  Result := FMessages.Count;
end;

function TBotMessageArray.GetMessage(const Index: Integer): TBotMessage;
begin
  Result := FMessages[Index];
end;

{ TBotChannel }

constructor TBotChannel.Create;
begin

end;

destructor TBotChannel.Destroy;
begin

  inherited;
end;

{ TBotChannelArray }

constructor TBotChannelArray.Create;
begin

end;

destructor TBotChannelArray.Destroy;
begin

  inherited;
end;

{ TBotUser }

constructor TBotUser.Create;
begin

end;

{ TBotUserArray }

constructor TBotUserArray.Create;
begin

end;

destructor TBotUserArray.Destroy;
begin

  inherited;
end;

end.