unit Main;

interface

uses
  Windows,
  SysUtils,
  Classes,
  Graphics,
  Controls,
  Forms,
  Dialogs,
  Sockets,
  StdCtrls,
  DateUtils,
  uLkJSON;

type

  TClientThread = class;

  TFormMain = class(TForm)
    MemoTraffic: TMemo;
    Button1: TButton;
    procedure FormCreate(Sender: TObject);
    procedure Button1Click(Sender: TObject);
  private
    FThread: TClientThread;
    procedure ThreadHandler(const S: string);
  end;

  TClientThread = class(TThread)
  private
    FBuffer: string;
    FHandler: TGetStrProc;
  public
    constructor Create(CreateSuspended: Boolean);
    procedure Update;
    procedure Execute; override;
  public
    property Handler: TGetStrProc read FHandler write FHandler;
  end;

var
  FormMain: TFormMain;

implementation

{$R *.dfm}

{ TClientThread }

constructor TClientThread.Create(CreateSuspended: Boolean);
begin
  inherited;
  FreeOnTerminate := True;
end;

procedure TClientThread.Execute;
var
  Client: TTcpClient;
begin
  try
    Client := TTcpClient.Create(nil);
    try
      Client.RemoteHost := '192.168.1.58'; // exception raised and program hangs if address is inaccessible  >:-|
      Client.RemotePort := '50000';
      if Client.Connect = False then
      begin
        ShowMessage('Unable to connect to remote host.');
        Exit;
      end;
      while (Application.Terminated = False) and (Self.Terminated = False) and (Client.Connected = True) do
      begin
        FBuffer := Client.Receiveln(#10);
        Synchronize(Update);
      end;
    finally
      Client.Free;
    end;
  except
    on E: Exception do
      ShowMessage('Exception' + ^M + E.ClassName + ^M + E.Message);
  end;
end;

procedure TClientThread.Update;
begin
  if Assigned(FHandler) then
    FHandler(FBuffer);
end;

{ TFormMain }

procedure TFormMain.FormCreate(Sender: TObject);
begin
  FThread := TClientThread.Create(True);
  FThread.Handler := ThreadHandler;
  FThread.Resume;
end;

procedure TFormMain.Button1Click(Sender: TObject);
begin
  FThread.Terminate;
end;

procedure TFormMain.ThreadHandler(const S: string);
var
  json: uLkJSON.TlkJSONbase;
  json_obj: uLkJSON.TlkJSONobject;

  msg_buf: string;
  msg_type: string;
  msg_time: Double;
  msg_time_dt: TDateTime;
  msg_time_str: string;
  
  msg_command: string;
  msg_pid: Integer;
  msg_alias: string;
  msg_template: string;
  msg_allow_empty: string;
  msg_timeout: string;
  msg_repeat: string;
  msg_auto_privmsg: string;
  msg_start: Double;
  msg_nick: string;
  msg_cmd: string;
  msg_destination: string;
  msg_trailing: string;
  msg_server: string;
  msg_data: string;
  msg_prefix: string;
  msg_params: string;
  msg_user: string;
  msg_hostname: string;
  msg_accounts_wildcard: string;
  msg_exec_line: string;
  msg_file: string;
  
  //msg_bucket_locks
  //msg_accounts
  //msg_cmds
  //msg_dests

begin
  json := uLkJSON.TlkJSON.ParseText(S);

  json_obj := json as uLkJSON.TlkJSONobject;

  msg_buf := json_obj.getString('buf');
  msg_type := json_obj.getString('type');
  msg_time := json_obj.getDouble('time');
  msg_time_dt := DateUtils.UnixToDateTime(Round(msg_time));
  msg_time_str := SysUtils.FormatDateTime('yyyy-mm-dd hh:nn:ss', msg_time_dt);
  if (msg_type = 'stdout') or (msg_type = 'stderr') then
  begin
    msg_command := json_obj.getString('command');
    msg_pid := json_obj.getInt('pid');
    msg_alias := json_obj.getString('alias');
    msg_template := json_obj.getString('template');
    msg_allow_empty := json_obj.getString('allow_empty');
    msg_timeout := json_obj.getString('timeout');
    msg_repeat := json_obj.getString('repeat');
    msg_auto_privmsg := json_obj.getString('auto_privmsg');
    msg_start := json_obj.getDouble('start');
    msg_nick := json_obj.getString('nick');
    msg_cmd := json_obj.getString('cmd');
    msg_destination := json_obj.getString('destination');
    msg_trailing := json_obj.getString('trailing');

    msg_server := json.Field['items'].Field['server'].Value;

    {msg_data := json_obj.getString('');
    msg_prefix := json_obj.getString('');
    msg_params := json_obj.getString('');
    msg_user := json_obj.getString('');
    msg_hostname := json_obj.getString('');
    msg_accounts_wildcard := json_obj.getString('');
    msg_exec_line := json_obj.getString('');
    msg_file := json_obj.getString('');}
    
    //msg_bucket_locks
    //msg_accounts
    //msg_cmds
    //msg_dests
  end
  else
  begin

  end;
  MemoTraffic.Lines.Text := SysUtils.Trim(msg_buf) + '[' + msg_server + ']';
  json.Free;
end;

end.
