/*
 * Copyright © 2010 Orsa Studio (www.orsa-studio.com)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
package com.orsa.slimstack.app;

import com.orsa.slimstack.runner.DestroyProcessRunner;
import com.orsa.slimstack.runner.OutputReader;
import com.orsa.slimstack.runner.SbtRunner;
import javax.imageio.ImageIO;
import javax.swing.*;
import java.awt.*;
import java.awt.event.*;
import java.awt.image.BufferedImage;
import java.io.*;

// @todo read http://www.coderanch.com/t/474276/GUI/java/questions-system-tray-tray-icon
// @todo read http://stackoverflow.com/questions/309023/howto-bring-a-java-window-to-the-front

public class SLiMStack extends JFrame 
{
    private JTextArea textAreaMongoDB;
    private JTextArea textAreaApache;

    JTextArea textArea = null;
    PipedInputStream piOut;
    PipedInputStream piErr;
    PipedOutputStream poOut;
    PipedOutputStream poErr;
    SLiMStackUI ui;

    JEditorPane newsPane = null;

    public SLiMStack()
    {
//        JLabel jlbHelloWorld = new JLabel("Hello World");
//		add(jlbHelloWorld);

        this.setLayout(new FlowLayout());

        JPanel slimStack = new JPanel();
        add(slimStack);

        Container cp = this.getContentPane();
        cp.setBackground(Color.white);

        setTitle("SLiM Stack");
        setResizable(false);

        setLayout(new FlowLayout());

        Image favIcon = Toolkit.getDefaultToolkit().getImage("image/favicon.png");
        setIconImage(favIcon);

        JPanel imgPanel = new JPanel();
        imgPanel.setBackground(Color.white);
        ImagePanel img = new ImagePanel("image/slimstack.png");
        imgPanel.add(img);
        LinkLabel link = new LinkLabel("SLiM Stack Home Page", "http://www.getslimstack.net/");
        link.setPreferredSize(new Dimension(135, img.getHeight()-3));
        link.setVerticalAlignment(SwingConstants.BOTTOM);
        imgPanel.add(link);
        slimStack.add(imgPanel);

        Font font = new Font("Verdana", Font.BOLD, 12);
//        txt.setForeground(Color.BLUE);

        textArea = new JTextArea(32, 33);
        textArea.setFont(font);
        textArea.setText("");
        textArea.setLineWrap(true);
        textArea.setWrapStyleWord(false);        
        textArea.setEditable(false);
        //textArea.setSize(390, 100);
        JScrollPane scrollPane = new JScrollPane(textArea);
        //add(scrollPane);

        JTabbedPane tabbedPane = new JTabbedPane();
        tabbedPane.setPreferredSize(new Dimension(376,447+90));
        tabbedPane.setMaximumSize(new Dimension(376,447+90));

        //tabbedPane.add(scrollPane);
        newsPane = new JEditorPane();
        newsPane.setEditable(false); // Read-only
        JScrollPane newsScroll = new JScrollPane(newsPane);
        try {
            newsPane.setPage("http://www.getslimstack.net/news.php?version=" + SLiMStackUI.version);
        } catch (IOException e) {
            e.printStackTrace();  //To change body of catch statement use File | Settings | File Templates.
        }

        tabbedPane.addTab("News", null, newsScroll, "");

        tabbedPane.addTab("Liftweb/Scala/SBT", null, scrollPane, "");

        textAreaMongoDB = new JTextArea(32, 33);
        textAreaMongoDB.setFont(font);

        textAreaMongoDB.setLineWrap(true);
        textAreaMongoDB.setWrapStyleWord(false);
        textAreaMongoDB.setEditable(false);
        JScrollPane scrollMongoDb = new JScrollPane(textAreaMongoDB);
        tabbedPane.addTab("MongoDB", null, scrollMongoDb, "");

        textAreaApache = new JTextArea(32, 33);
        textAreaApache.setFont(font);
        textAreaApache.setLineWrap(true);
        textAreaApache.setWrapStyleWord(false);
        textAreaApache.setEditable(false);
        //JScrollPane scrollApache = new JScrollPane(textAreaApache);
        //tabbedPane.addTab("Apache", null, scrollApache, "");

        ui = new SLiMStackUI(textArea, textAreaMongoDB, textAreaApache, tabbedPane);
        ui.instance = ui;
        slimStack.add(ui.panel1);

        try {
            ui.startSBT();
        } catch (IOException e) {
            e.printStackTrace();  //To change body of catch statement use File | Settings | File Templates.
        }

        slimStack.add(tabbedPane);

		slimStack.setSize(400, 800);
        slimStack.setMinimumSize(new Dimension(400, 800));
        slimStack.setPreferredSize(new Dimension(400, 800));
        slimStack.setBackground(Color.white);
        this.setSize(400,800);
		// pack();
        this.setLocationRelativeTo(null);
		setVisible(false);

        // http://fifesoft.com/rsyntaxtextarea/examples/example1.php

        PopupMenu popup = new PopupMenu();
        final TrayIcon trayIcon;
        trayIcon = new TrayIcon(favIcon, "SLiM Stack", popup);

        if (SystemTray.isSupported()) {

            SystemTray tray = SystemTray.getSystemTray();

            MouseListener mouseListener = new MouseListener() {
                
                public void mouseClicked(MouseEvent e) {


                    setVisible(true);
                    System.out.println("Tray Icon - Mouse clicked!");                 
                }
                public void mouseEntered(MouseEvent e) {

                    System.out.println("Tray Icon - Mouse entered!");
                }
                public void mouseExited(MouseEvent e) {
                    System.out.println("Tray Icon - Mouse exited!");                 
                }
                public void mousePressed(MouseEvent e) {
                    System.out.println("Tray Icon - Mouse pressed!");                 
                }
                public void mouseReleased(MouseEvent e) {
                    System.out.println("Tray Icon - Mouse released!");                 
                }

            };

            ActionListener exitListener = new ActionListener() {
                public void actionPerformed(ActionEvent e) {
                    stopSBT();
                    System.out.println("Exiting...");
                    System.exit(0);
                }
            };

/*            ActionListener sbtListener = new ActionListener() {
                public void actionPerformed(ActionEvent e) {

                }
            };
  */

            ActionListener stopApacheListener = new ActionListener() {
                public void actionPerformed(ActionEvent e) {
                    //run.stopApache();
                }
            };

            MenuItem defaultItem = new MenuItem("Exit");

            //MenuItem sbtMenuItem = new MenuItem("SBT");
            //sbtMenuItem.addActionListener(sbtListener);

            //MenuItem stopApache = new MenuItem("Stop Apache");
            //stopApache.addActionListener(stopApacheListener);

//            MenuItem startApache = new MenuItem("Start Apache");
//            startApache.addActionListener(startApacheActionListener);

            defaultItem.addActionListener(exitListener);
//            popup.add(startApache);
            //popup.add(stopApache);
            //popup.add(sbtMenuItem);
            popup.add(defaultItem);


            ActionListener actionListener = new ActionListener() {
                public void actionPerformed(ActionEvent e) {
/*                    trayIcon.displayMessage("Action Event",
                        "An Action Event Has Been Peformed!",
                        TrayIcon.MessageType.INFO);*/
                }
            };
            
            trayIcon.setImageAutoSize(true);
            trayIcon.addActionListener(actionListener);
            trayIcon.addMouseListener(mouseListener);

            //    Depending on which Mustang build you have, you may need to uncomment
            //    out the following code to check for an AWTException when you add 
            //    an image to the system tray.

                try {
                      tray.add(trayIcon);
                } catch (AWTException e) {
                    System.err.println("TrayIcon could not be added.");
                }

        } else {
            System.err.println("System tray is currently not supported.");
        }

        //shutdownHook = new Thread(new DestroyProcessRunner(process));
    }
/*
    private Thread shutdownHook;
    public void destroyOnShutdown() {
        Runtime.getRuntime().addShutdownHook(shutdownHook);
    }*/

    //RunSbt sbtRun = new RunSbt();

    private void runSbtThread() {
    }

    public void stopSBT() {
        ui.stopAllChildProcesses();
        ui.runner.destroy();
    }

     /*
      private void printToMessageWindow() {
        // org.jetbrains.idea.maven.execution.MavenExecutor#myConsole
        SbtConsole console = new SbtConsole(MessageBundle.message("sbt.tasks.action"), myProject);
        SbtProcessHandler process = new SbtProcessHandler(this, runner.subscribeToOutput());
        console.attachToProcess(process);
        process.startNotify();
    }  */
    
    /**
     * @param args the command line arguments
     */
    public static void main(String[] args)
    {
        SLiMStack main = new SLiMStack();
    }
    
}
