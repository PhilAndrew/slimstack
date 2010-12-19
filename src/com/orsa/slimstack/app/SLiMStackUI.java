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

import com.orsa.slimstack.runner.OutputReader;
import com.orsa.slimstack.runner.SbtRunner;
import org.ini4j.Wini;

import javax.swing.*;
import javax.swing.border.Border;
import javax.swing.border.CompoundBorder;
import javax.swing.border.EmptyBorder;
import javax.swing.border.LineBorder;
import java.awt.*;
import java.awt.event.ActionEvent;
import java.awt.event.ActionListener;
import java.awt.event.ItemEvent;
import java.awt.event.ItemListener;
import java.beans.PropertyChangeEvent;
import java.beans.PropertyChangeListener;
import java.io.*;
import java.net.URI;
import java.net.URISyntaxException;
import java.nio.channels.FileChannel;
import java.util.Date;
import java.util.HashSet;
import java.util.prefs.Preferences;

public class SLiMStackUI implements ActionListener, PropertyChangeListener {

    public static String version = "0.1";

    public JPanel panel1;
    private JButton startApacheButton;
    private JButton startMongodbButton;
    private JButton browseRockmongo;
    private JButton startSbtButton;
    private JButton browseLiftweb;
    private JButton settingsButton;
    private LinkLabel getJRebelButton;
    private JButton openFileExplorerButton;
    private JButton editFilesButton;
    private JCheckBox automaticStartOfAllCheckBox;
    private JButton addJARButton;
    private JButton addLicenceButton;
    private JPanel getJRebel;
    private JButton formButton;
    private JButton tableViewButton;
    private JButton DBTableButton;
    private JButton relationshipButton;
    private JButton deployButton;

    private Boolean firstTimeRun = false;
    // link = new LinkLabel("SLiM Stack Home Page", "http://www.getslimstack.net/");

    private HashSet<JButton> blinking;
    private int blinkCount = 0;
    private boolean mongoDbStarted = false;
    private boolean apacheStarted = false;
    private boolean sbtStarted = false;
    private boolean sbtStartingUp = false;
    private Thread threadSbtRun = null;

    SbtRunner runner;
    OutputReader output;
    private JTextArea textArea;
    private JTabbedPane tabbedPane;
    //private EditorFrame editorFrame;

    private boolean shouldStartupAllApps = false;

    // This is for blinking
    public void actionPerformed(ActionEvent e) {
        blinkCount ++;
        if (shouldStartupAllApps)
        {
            shouldStartupAllApps = false;
            startApacheNow();
            startSBTAndMongoDB();
        }

        for (JButton blink : blinking)
        {
            if ((blinkCount%2) == 0)
                blink.setBackground(Color.white);
            else
                blink.setBackground(Color.lightGray);
        }
    }

    public void stopAllChildProcesses() {
        if (runApache!=null)
            runApache.stopApache();
        if (runMongodb!=null)
            runMongodb.stopApache();
    }

    public void propertyChange(PropertyChangeEvent evt) {
        if ("progress" == evt.getPropertyName()) {
            int progress = (Integer) evt.getNewValue();

            int currentProgress = progressBar.getValue();
            //progress = Math.max(currentProgress, currentProgress);
            if ((progress <= currentProgress) && (progress > 1))
               return;
            progressBar.setValue(progress);
        }
    }

    class RunSbt implements Runnable {
        public void run() {
            try {
                if (firstTimeRun)
                {
                    runner.execute("update");
                }

                runner.execute("jetty-run");

                // Now it started, need to display this on the button
                blinking.remove(startSbtButton);
                startSbtButton.setBackground(Color.white);
                startSbtButton.setText("Stop");
                sbtStartingUp = false;
                sbtStarted = true;

                // Only if we want to monitor and reload the application
                runner.execute("~ prepare-webapp");
            } catch (IOException e1) {
                e1.printStackTrace();  //To change body of catch statement use File | Settings | File Templates.
            }

            System.out.print("End?");
        }
    }

    private static JButton adjustButton(JButton button) {
     // JButton button = new JButton(text);
      button.setFocusPainted(false);
      button.setForeground(Color.BLACK);
      //button.setBackground(Color.WHITE);
      button.setBackground(Color.lightGray);
      Border line = new LineBorder(Color.BLACK);
      Border margin = new EmptyBorder(5, 15, 5, 15);
      Border compound = new CompoundBorder(line, margin);
      button.setBorder(compound);
      return button;
    }

    RunSbt sbtRun = new RunSbt();


    RunApache runApache = null;
    private RunMongodb runMongodb;

    Desktop desktop = null;

    private JTextArea textAreaMongoDB;
    private JTextArea textAreaApache;

    public SLiMStackUI instance;//hack

    public SLiMStackUI(JTextArea textArea, final JTextArea textAreaMongoDB, final JTextArea textAreaApache, final JTabbedPane tabbedPane) {

        String liftWebTextAreaNotice = "";

        blinking = new HashSet<JButton>();
        //getJRebelButton = new LinkLabel("Get JRebel", "http://www.zeroturnaround.com/blog/free-javarebel-for-scala-users-zeroturnaround-announces/");
        //getJRebel.add(getJRebelButton);

        this.tabbedPane = tabbedPane;
        this.textArea = textArea;
        this.textAreaMongoDB = textAreaMongoDB;
        this.textAreaApache = textAreaApache;

        // Before more Desktop API is used, first check
        // whether the API is supported by this particular
        // virtual machine (VM) on this particular host.
        if (Desktop.isDesktopSupported()) {
            desktop = Desktop.getDesktop();
        }

        //startApacheButton.setBorder(new Border());ound(Color.red);
        //Border thickBorder = new LineBorder(Color.BLACK, 5);
        //startApacheButton.setBorder(thickBorder);

        adjustButton(startApacheButton);
        adjustButton(startMongodbButton);
        adjustButton(browseRockmongo);
        adjustButton(startSbtButton);
        adjustButton(browseLiftweb);
//        adjustButton(settingsButton);
//        adjustButton(getJRebelButton);
        adjustButton(openFileExplorerButton);
        adjustButton(editFilesButton);
//      adjustButton(addJARButton);
//        adjustButton(addLicenceButton);


        automaticStartOfAllCheckBox.setFocusPainted(false);
        automaticStartOfAllCheckBox.setBackground(Color.white);

        // Check if JRebel is installed
        try {
            String jrebelJar = new File(".").getCanonicalPath() + "\\jrebel\\jrebel.jar";
            if (new File(jrebelJar).exists())
                copyFileFromTo(new File(".").getCanonicalPath() + "\\liftweb\\project\\build\\LiftProject.jrebel.txt",
                        new File(".").getCanonicalPath() + "\\liftweb\\project\\build\\LiftProject.scala");
            else
                copyFileFromTo(new File(".").getCanonicalPath() + "\\liftweb\\project\\build\\LiftProject.normal.txt",
                        new File(".").getCanonicalPath() + "\\liftweb\\project\\build\\LiftProject.scala");
        } catch (IOException e) {
            e.printStackTrace();  //To change body of catch statement use File | Settings | File Templates.
        }

        Wini ini = null;
        try {
            ini = new Wini(new File("slimstack.ini"));
            shouldStartupAllApps = ini.get("startup", "autoStart", boolean.class);
            firstTimeRun = ini.get("startup", "firsttimerun", boolean.class);
            if (firstTimeRun)
            {
                liftWebTextAreaNotice = "**** NOTICE ****\r\nONLY on the first time run, all JAR files must be downloaded.\r\n" +
                        "This may take a while! Please wait for the download to complete before SLiMStack is usable.\r\n\r\n" +
                        "When SLiMStack is run again later, the startup will be faster.\r\n\r\n";
            }
            automaticStartOfAllCheckBox.setSelected(shouldStartupAllApps);
        } catch (IOException e) {
            e.printStackTrace();
        }

        // Need to rewrite this to set the first time run to false
        try {
            ini = new Wini(new File("slimstack.ini"));
            ini.put("startup", "autoStart", shouldStartupAllApps);
            ini.put("startup", "firsttimerun", false);
            ini.store();
        } catch (IOException e1) {
            e1.printStackTrace();  //To change body of catch statement use File | Settings | File Templates.
        }

        textArea.setText(liftWebTextAreaNotice);

        automaticStartOfAllCheckBox.addItemListener(
                new ItemListener() {
                    public void itemStateChanged(ItemEvent e) {
                        // Save to file
                        Boolean checked = (e.getStateChange() == ItemEvent.SELECTED);
                        try {
                            Wini ini = new Wini(new File("slimstack.ini"));
                            ini.put("startup", "autoStart", checked);
                            ini.put("startup", "firsttimerun", false);
                            ini.store();
                        } catch (IOException e1) {
                            e1.printStackTrace();  //To change body of catch statement use File | Settings | File Templates.
                        }
                    }
                }
        );

        startSbtButton.addActionListener(new ActionListener() {
            public void actionPerformed(ActionEvent e) {
                if (sbtStarted==false)
                {
                    if (sbtStartingUp==false)
                    {
                        Boolean p8080Available = QueryPortInUse.available(8080);
                        Boolean p8081Available = QueryPortInUse.available(8081);
                        Boolean p27017Available = QueryPortInUse.available(27017);

                        if (p8080Available)
                            startSBTAndMongoDB();
                        else
                        {
                            int answer = JOptionPane.showConfirmDialog(panel1,
                                    "Port 8080 is currently in use by an application\nDo you still wish to start SBT?",
                                    "Start SBT",
                                    JOptionPane.YES_NO_OPTION);
                            if (answer==JOptionPane.YES_OPTION)
                            {
                                startSBTAndMongoDB();
                            }
                        }
                    } else
                    {
                        JOptionPane.showMessageDialog(panel1, "SBT is currently starting up.\nPlease wait.");
                    }
                } else
                {
                    threadSbtRun.interrupt();
                    if (runner!=null)
                        runner.destroy();
                    //sbtRun = null;
                    sbtStarted = false;
                    sbtStartingUp = false;
                    darkButton(startSbtButton);
                    startSbtButton.setText("Start");
// @todo Stop it ####
/*                    sbtRun = new RunSbt();
                    new Thread(sbtRun).start();
                    blinking.add(startSbtButton);
                    startSbtButton.setText("Starting");
*/
                }
            }
        });

        startApacheButton.addActionListener(new ActionListener() {
            public void actionPerformed(ActionEvent e) {
                startApacheNow();
            }
        });
        startMongodbButton.addActionListener(new ActionListener() {
            public void actionPerformed(ActionEvent e) {
                // Are we starting or stopping it?
                if (mongoDbStarted==false)
                {
                    startMongoDbNow();
                } else {
                    runMongodb.stopApache();
                    darkButton(startMongodbButton);
                    startMongodbButton.setText("Start");
                    mongoDbStarted = false;
                }
            }
        });
        browseLiftweb.addActionListener(new ActionListener() {
            public void actionPerformed(ActionEvent e) {
                URI uri = null;
                try {
                    uri = new URI("http://localhost:8080/");
                    desktop.browse(uri);
                }
                catch(IOException ioe) {
                    ioe.printStackTrace();
                }
                catch(URISyntaxException use) {
                    use.printStackTrace();

                }
            }
        });
        browseRockmongo.addActionListener(new ActionListener() {
            public void actionPerformed(ActionEvent e) {
                URI uri = null;
                try {
                    uri = new URI("http://localhost:8080/rockmongo/index.php");
                    desktop.browse(uri);
                }
                catch(IOException ioe) {
                    ioe.printStackTrace();
                }
                catch(URISyntaxException use) {
                    use.printStackTrace();

                }
            }
        });

        openFileExplorerButton.addActionListener(new ActionListener() {
            public void actionPerformed(ActionEvent e) {
                try {
                    String path = new File(".").getCanonicalPath();
                    desktop.open(new File(path + "/liftweb/src/main"));
                } catch (IOException e1) {
                    e1.printStackTrace();  //To change body of catch statement use File | Settings | File Templates.
                }
            }
        });

        editFilesButton.addActionListener(new ActionListener() {
            public void actionPerformed(ActionEvent e) {
                try {
                    String path = new File(".").getCanonicalPath();
                    desktop.open(new File(path + "/liftweb"));
                } catch (IOException e1) {
                    e1.printStackTrace();  //To change body of catch statement use File | Settings | File Templates.
                }
            }
        });

        checkOpenPorts();

        startBlinking();

        addStatusWindow();
    }

    private void copyFileFromTo(String fromFile, String toFile) throws IOException {
        File from = new File(fromFile);
        File to = new File(toFile);
        to.delete();
        to = new File(toFile);

        copyFile(from, to);
    }

    public static void copyFile(File sourceFile, File destFile) throws IOException {
        if(!destFile.exists()) {
            destFile.createNewFile();
        }

        FileChannel source = null;
        FileChannel destination = null;
        try {
            source = new FileInputStream(sourceFile).getChannel();
            destination = new FileOutputStream(destFile).getChannel();
            destination.transferFrom(source, 0, source.size());
        }
        finally {
            if(source != null) {
            source.close();
            }
            if(destination != null) {
            destination.close();
            }
        }
    }

    JFrame frame = null;

    class UpdateProgress extends SwingWorker<Void, Void> {
        /*
         * Main task. Executed in background thread.
         */
        @Override
        public Void doInBackground() {
            int progress = 0;
            //Initialize progress property.
            setProgress(0);
            while (progress < 100) {
                //Sleep for up to one second.
                try {
                    Thread.sleep(100);
                } catch (InterruptedException ignore) {}
                //Make random progress.
                progress += 1; //random.nextInt(10);
                setProgress(Math.min(progress, 100));
            }
            return null;
        }

        /*
         * Executed in event dispatching thread
         */
        @Override
        public void done() {
        }
    }

    UpdateProgress updateProgress = null;
    JProgressBar progressBar = null;

    private void addStatusWindow() {
        frame = new JFrame("SLim Stack Reloading");

        Image favIcon = Toolkit.getDefaultToolkit().getImage("image/favicon.png");
        frame.setIconImage(favIcon);

        frame.setUndecorated(true);
        frame.setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);
        // Set's the window to be "always on top"
        frame.setAlwaysOnTop( true );
        Dimension screenSize = Toolkit.getDefaultToolkit().getScreenSize();

        UIManager.put("ProgressBar.selectionBackground", Color.black);
        UIManager.put("ProgressBar.selectionForeground", Color.black);
        progressBar = new JProgressBar(0, 100);
        progressBar.setValue(1);
        progressBar.setForeground(Color.YELLOW);
        progressBar.setSize(150, 20);
        progressBar.setStringPainted(true);
        progressBar.setBackground(Color.GRAY);
        progressBar.setString("SLiM Stack Reloading");

        frame.add(progressBar);
        //frame.add( new JLabel("  Isn't this annoying?") );
        frame.pack();
        //frame.setSize(200, 20);
        frame.setLocation(screenSize.width - 150, 1);
        frame.setVisible(false);
    }

    private Boolean progressBarStarted = false;
    private Date lastStart = new Date();

    private void startProgress() {
        synchronized (this) {
            // Prevent from starting twice
            if (progressBarStarted)
                return;

            Date now = new Date();
            Long diff = now.getTime() - lastStart.getTime();
            // Prevent starting if the time diff is smaller than 3 seconds
            if (diff < 3000)
              return;
            lastStart = new Date();

            // Start the progress bar
            progressBar.setValue(0);
            updateProgress = new UpdateProgress();
            updateProgress.addPropertyChangeListener(this);
            updateProgress.execute();
            frame.setVisible(true);

            progressBarStarted = true;
        }
    }

    private void stopProgress() {
        synchronized (this) {
            if (progressBarStarted)
            {
                if (updateProgress!=null)
                    updateProgress.cancel(true);
                frame.setVisible(false);
                progressBarStarted = false;
                lastStart = new Date();
            }
        }
    }

    private void startApacheNow() {
        if (apacheStarted==false)
        {
            runApache = new RunApache(textAreaApache);
            new Thread(runApache).start();
            greenButton(startApacheButton);
            startApacheButton.setText("Stop");
            apacheStarted = true;
        } else
        {
            runApache.stopApache();
            darkButton(startApacheButton);
            startApacheButton.setText("Start");
            apacheStarted = false;
        }
    }

    private void startSBTAndMongoDB() {
        // Also start mongodb as it is required
        startMongoDbNow();

        tabbedPane.setSelectedIndex(1);
        try {
            startSBT();
        } catch (IOException e1) {
            e1.printStackTrace();  //To change body of catch statement use File | Settings | File Templates.
        }
        sbtRun = new RunSbt();
        threadSbtRun = new Thread(sbtRun);
        threadSbtRun.start();
        blinking.add(startSbtButton);
        startSbtButton.setBackground(Color.lightGray);
        startSbtButton.setText("Wait");
        sbtStartingUp = true;
    }

    private void checkOpenPorts() {
        Boolean p8080Available = QueryPortInUse.available(8080);
        Boolean p8081Available = QueryPortInUse.available(8081);
        Boolean p27017Available = QueryPortInUse.available(27017);

        if (p8080Available==false)
            startSbtButton.setBackground(Color.red);

        if (p8081Available==false)
            startApacheButton.setBackground(Color.red);

        if (p27017Available==false)
            startMongodbButton.setBackground(Color.red);
    }

    private void startMongoDbNow() {
        if (mongoDbStarted==false)
        {
            Boolean p8080Available = QueryPortInUse.available(8080);
            Boolean p8081Available = QueryPortInUse.available(8081);
            Boolean p27017Available = QueryPortInUse.available(27017);
            Boolean startMongodb = false;

            if (p27017Available==false)
            {
                int answer = JOptionPane.showConfirmDialog(panel1,
                        "Port 27017 is currently in use by an application\nThis port is required by MongoDB\nDo you still wish to start MongoDB?",
                        "Start MongoDB",
                        JOptionPane.YES_NO_OPTION);
                if (answer==JOptionPane.YES_OPTION)
                {
                    startMongodb = true;
                }
            } else
                startMongodb = true;

            if (startMongodb)
            {
                tabbedPane.setSelectedIndex(2);
                runMongodb = new RunMongodb(textAreaMongoDB);
                new Thread(runMongodb).start();
                greenButton(startMongodbButton);
                startMongodbButton.setText("Stop");
                mongoDbStarted = true;
            }
        }
    }

    private void darkButton(JButton startMongodb) {
        startMongodb.setBackground(Color.lightGray);
    }

    private void startBlinking() {
        Timer timer = new Timer( 250, this);
        timer.setInitialDelay(0);
        timer.start();
    }


    private void greenButton(JButton button) {
        //Border greenBorder = new LineBorder(Color.GREEN, 5);
        //button.setBorder(greenBorder);
        button.setBackground(Color.white);
        button.setText("Started");

    }

    class TextAreaOutput implements Runnable {

        TextAreaOutput(String out) {
            output = out;
        }

        private String output = "";

        public void run() {
            textArea.append(output);

            // Make sure the last line is always visible
            textArea.setCaretPosition(textArea.getDocument().getLength());

            // Keep the text area down to a certain character size
            int idealSize = 50000;
            int maxExcess = 500;
            int excess = textArea.getDocument().getLength() - idealSize;
            if (excess >= maxExcess) {
                textArea.replaceRange("", 0, excess);
            }

            // Important to pick up if the application is refreshing or not
        }
    }

    class ReaderThread extends Thread {
        InputStream pi;

        ReaderThread(InputStream pi) {
            this.pi = pi;
        }

        public void run() {
            final byte[] buf = new byte[1024];

            // Keep track of a line of text so we can detect if the compile starts or stops
            // == compile ==
            // time:
            StringBuffer line = new StringBuffer("");

            try {
                while (true) {
                    final int len = pi.read(buf);
                    if (len == -1) {
                        break;
                    }
                    String out = new String(buf, 0, len);

                    line.append(out);

                    if ((out.indexOf("[succ")>=0) ||
                        (out.indexOf("[info] Total time:")>=0) ||
                        (out.indexOf("ccess]")>=0) ||
                        (out.indexOf("sful.")>=0))
                        stopProgress();
                    else
                    if (out.indexOf("Source analysis:")>=0)
                        startProgress();

                    // if /r registers twice, clear the line

                    if (line.toString().replaceAll("[^\r]", "").length() >= 2)
                        line = new StringBuffer("");

                    SwingUtilities.invokeLater(new TextAreaOutput(out));
                }
            } catch (IOException e) {
            }
        }
    }

    public void startSBT() throws IOException {
        File launcherJar = new File(new File(".").getCanonicalPath() + "/lib/sbt-launch-0.7.4.jar"); // sbt_2.8.0-SNAPSHOT.jar
        File liftweb = new File(new File(".").getCanonicalPath() + "/liftweb");
        runner = new SbtRunner(liftweb, launcherJar);
        try {
            runner.start();
            output = runner.subscribeToOutput();

            // Set up System.out
            try {
               /* piOut = new PipedInputStream();
                poOut = new PipedOutputStream(piOut);
                System.setOut(new PrintStream(poOut, true));

                // Set up System.err
                piErr = new PipedInputStream();
                poErr = new PipedOutputStream(piErr);
                System.setErr(new PrintStream(poErr, true));*/

                //ReaderInputStream r = ;
                new ReaderThread(new ReaderInputStream(output)).start();

                //new ReaderThread(piErr).start();
            } catch (IOException e) {
                e.printStackTrace();  //To change body of catch statement use File | Settings | File Templates.
            }
        } catch (IOException e) {
            e.printStackTrace();  //To change body of catch statement use File | Settings | File Templates.
        }
    }

}
