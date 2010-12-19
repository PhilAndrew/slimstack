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

import javax.swing.*;
import java.io.*;
import java.util.ArrayList;
import java.util.List;
import java.util.Map;

public class RunApache implements Runnable {

    private JTextArea textAreaApache;

    public RunApache(JTextArea textAreaApache) {
        this.textAreaApache = textAreaApache;
    }

    private static String[] getCommand(String path) throws IOException {
        return new String[]{
                new File(".").getCanonicalPath() + "\\php\\php-5.3.3-Win32-VC9-x86\\php-cgi.exe", "-b8081"
                // "\\home\\projects\\SLiMStack\\php\\php-5.3.3-Win32-VC9-x86\\php-cgi.exe", "-b8081"
                //"cmd.exe", "/C", "httpd.exe"
        };
    }

    Process process = null;


    public void runApache() throws IOException {
        Runtime.getRuntime().addShutdownHook(
            new Thread(
                new Runnable() {
                    public void run() {
                        stopApache();
                    }
                }
            )
        );

        String path = new File(".").getCanonicalPath() + "\\php\\php-5.3.3-Win32-VC9-x86";

/*        File file = new File(path);
        Runtime runtime = Runtime.getRuntime();
        Process process = runtime.exec("cmd.exe", null, file);*/

        ProcessBuilder builder = new ProcessBuilder(getCommand(path));
        //Map<String, String> environ = builder.environment();
        builder.directory(new File(path));
        process = builder.start();
        InputStream is = process.getInputStream();
        InputStreamReader isr = new InputStreamReader(is);
        BufferedReader br = new BufferedReader(isr);
        String line;
        while ((line = br.readLine()) != null) {

            final String finalLine = line;
            SwingUtilities.invokeLater(new Runnable() {
                public void run() {
                    textAreaApache.append(new String(finalLine + "\n"));

                    // Make sure the last line is always visible
                    textAreaApache.setCaretPosition(textAreaApache.getDocument().getLength());

                    // Keep the text area down to a certain character size
                    int idealSize = 50000;
                    int maxExcess = 500;
                    int excess = textAreaApache.getDocument().getLength() - idealSize;
                    if (excess >= maxExcess) {
                        textAreaApache.replaceRange("", 0, excess);
                    }
                }
            });

        }
        System.out.println("Program terminated!");
    }

    public void stopApache() {

        process.destroy();
        try {
            process.waitFor();
        } catch (InterruptedException e) {
            e.printStackTrace();
        }
    }

    public void run() {
        try {
            runApache();
        } catch (IOException e) {
            e.printStackTrace();  //To change body of catch statement use File | Settings | File Templates.
        }
    }
}



