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

public class RunMongodb implements Runnable {

    private JTextArea textAreaMongoDB;

    public RunMongodb(JTextArea textAreaMongoDB) {
        this.textAreaMongoDB = textAreaMongoDB;
    }

    private static String[] getCommand(String path) throws IOException {
        return new String[]{
                new File(".").getCanonicalPath() + "\\mongodb\\mongodb-win32-i386-1.6.5\\bin\\mongod.exe", "--dbpath", "../../database"
                //"cmd.exe", "/C", "httpd.exe"
        };
    }

    Process process = null;

    public void runMongodb() throws IOException {

        Runtime.getRuntime().addShutdownHook(
            new Thread(
                new Runnable() {
                    public void run() {
                        stopApache();
                    }
                }
            )
        );

        String path = new File(".").getCanonicalPath() + "\\mongodb\\mongodb-win32-i386-1.6.5\\bin";

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
                    textAreaMongoDB.append(new String(finalLine + "\n"));

                    // Make sure the last line is always visible
                    textAreaMongoDB.setCaretPosition(textAreaMongoDB.getDocument().getLength());

                    // Keep the text area down to a certain character size
                    int idealSize = 50000;
                    int maxExcess = 500;
                    int excess = textAreaMongoDB.getDocument().getLength() - idealSize;
                    if (excess >= maxExcess) {
                        textAreaMongoDB.replaceRange("", 0, excess);
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
            runMongodb();
        } catch (IOException e) {
            e.printStackTrace();  //To change body of catch statement use File | Settings | File Templates.
        }
    }
}
