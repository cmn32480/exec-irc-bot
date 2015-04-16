object FormMain: TFormMain
  Left = 353
  Top = 157
  Width = 800
  Height = 500
  Caption = 'execstat'
  Color = clBtnFace
  Font.Charset = DEFAULT_CHARSET
  Font.Color = clWindowText
  Font.Height = -11
  Font.Name = 'Courier New'
  Font.Style = []
  Menu = MainMenu
  OldCreateOrder = False
  OnCreate = FormCreate
  PixelsPerInch = 96
  TextHeight = 14
  object LabelMessage: TLabel
    Left = 0
    Top = 421
    Width = 792
    Height = 14
    Align = alBottom
  end
  object Splitter1: TSplitter
    Left = 0
    Top = 251
    Width = 792
    Height = 3
    Cursor = crVSplit
    Align = alTop
    AutoSnap = False
    MinSize = 100
  end
  object StatusBar1: TStatusBar
    Left = 0
    Top = 435
    Width = 792
    Height = 19
    Panels = <
      item
        Width = 80
      end
      item
        Width = 120
      end
      item
        Width = 70
      end
      item
        Text = '0 errors'
        Width = 80
      end
      item
        Width = 150
      end
      item
        Width = 120
      end
      item
        Width = 120
      end>
  end
  object ProgressBar1: TProgressBar
    Left = 0
    Top = 405
    Width = 792
    Height = 16
    Align = alBottom
    Step = 1
    TabOrder = 1
  end
  object MemoTraffic: TMemo
    Left = 0
    Top = 254
    Width = 792
    Height = 97
    Align = alClient
    Color = clBtnFace
    ReadOnly = True
    ScrollBars = ssBoth
    TabOrder = 2
    WordWrap = False
  end
  object Panel1: TPanel
    Left = 0
    Top = 0
    Width = 792
    Height = 251
    Align = alTop
    BevelOuter = bvNone
    TabOrder = 3
    object Splitter2: TSplitter
      Left = 142
      Top = 0
      Height = 251
      AutoSnap = False
      MinSize = 100
    end
    object Splitter3: TSplitter
      Left = 272
      Top = 0
      Height = 251
      AutoSnap = False
      MinSize = 100
    end
    object Splitter4: TSplitter
      Left = 427
      Top = 0
      Height = 251
      AutoSnap = False
      MinSize = 100
    end
    object ListBoxBuckets: TListBox
      Left = 0
      Top = 0
      Width = 142
      Height = 251
      Align = alLeft
      ItemHeight = 14
      TabOrder = 0
      OnClick = ListBoxBucketsClick
    end
    object ListBoxAliases: TListBox
      Left = 145
      Top = 0
      Width = 127
      Height = 251
      Align = alLeft
      ItemHeight = 14
      TabOrder = 1
      OnClick = ListBoxAliasesClick
    end
    object ListBoxHandles: TListBox
      Left = 275
      Top = 0
      Width = 152
      Height = 251
      Align = alLeft
      ItemHeight = 14
      TabOrder = 2
      OnClick = ListBoxHandlesClick
    end
    object TreeViewData: TTreeView
      Left = 430
      Top = 0
      Width = 362
      Height = 251
      Align = alClient
      Indent = 19
      TabOrder = 3
    end
  end
  object Panel2: TPanel
    Left = 0
    Top = 351
    Width = 792
    Height = 54
    Align = alBottom
    BevelOuter = bvNone
    TabOrder = 4
    object LabeledEditAliasesDest: TLabeledEdit
      Left = 5
      Top = 20
      Width = 157
      Height = 22
      EditLabel.Width = 147
      EditLabel.Height = 14
      EditLabel.Caption = 'target channel / nick'
      TabOrder = 0
      Text = '#journals'
    end
    object LabeledEditAliasesTrailing: TLabeledEdit
      Left = 175
      Top = 20
      Width = 270
      Height = 22
      EditLabel.Width = 56
      EditLabel.Height = 14
      EditLabel.Caption = 'trailing'
      TabOrder = 1
      Text = '~say execstat test'
    end
    object ButtonSend: TButton
      Left = 449
      Top = 18
      Width = 75
      Height = 25
      Caption = 'Send'
      TabOrder = 2
      OnClick = ButtonSendClick
    end
    object Button3: TButton
      Left = 530
      Top = 18
      Width = 81
      Height = 25
      Caption = 'DISCONNECT'
      TabOrder = 3
      OnClick = Button3Click
    end
    object ButtonRunTests: TButton
      Left = 615
      Top = 18
      Width = 75
      Height = 25
      Caption = 'RUN TESTS'
      TabOrder = 4
      OnClick = ButtonRunTestsClick
    end
    object ButtonAliasesBuckets: TButton
      Left = 696
      Top = 18
      Width = 95
      Height = 25
      Caption = 'UPDATE LISTS'
      TabOrder = 5
      OnClick = ButtonAliasesBucketsClick
    end
  end
  object TimerStatus: TTimer
    Enabled = False
    Interval = 50
    OnTimer = TimerStatusTimer
    Left = 238
    Top = 186
  end
  object MainMenu: TMainMenu
    Left = 160
    Top = 189
    object MenuFile: TMenuItem
      Caption = '&File'
      object MenuItemExit: TMenuItem
        Caption = 'E&xit'
      end
    end
  end
  object TimerUpdateHandles: TTimer
    Enabled = False
    Interval = 3000
    OnTimer = TimerUpdateHandlesTimer
    Left = 56
    Top = 184
  end
end
