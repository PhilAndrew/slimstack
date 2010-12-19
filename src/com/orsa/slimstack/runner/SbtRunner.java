// Copyright © 2010, Esko Luontola <www.orfjackal.net>
// This software is released under the Apache License 2.0.
// The license text is at http://www.apache.org/licenses/LICENSE-2.0

package com.orsa.slimstack.runner;

import java.io.*;

/*

PLA:

starting sbt

launcherJar() is private static final String DEFAULT_SBT_LAUNCHER = canonicalPathTo(new File(System.getProperty("user.home"), "bin/sbt-launch.jar"));

    private void startIfNotStarted() throws IOException {
        if (sbt == null || !sbt.isAlive()) {
            sbt = new SbtRunner(projectDir(), launcherJar());
            printToMessageWindow();
            if (DEBUG) {
                printToLogFile();
            }
            sbt.start();
        }
    }

execute an action

        try {
            sbt.execute(action);

            // TODO: update target folders (?)
            // org.jetbrains.idea.maven.project.MavenProjectsManager#updateProjectFolders
            // org.jetbrains.idea.maven.execution.MavenRunner#runBatch
            // org.jetbrains.idea.maven.execution.MavenRunner#updateTargetFolders

            // TODO: synchronize changes to file system (?)

        } catch (IOException e) {
            destroyProcess();
            throw e;
        }


 */

public class SbtRunner {

    private static final String PROMPT = "\n> ";
    private static final String PROMPT_AFTER_EMPTY_ACTION = "> ";

    private final ProcessRunner sbt;

    public SbtRunner(File liftweb, File launcherJar) {
        if (!liftweb.isDirectory()) {
            throw new IllegalArgumentException("Working directory does not exist: " + liftweb);
        }
        if (!launcherJar.isFile()) {
            throw new IllegalArgumentException("Launcher JAR file does not exist: " + launcherJar);
        }
        sbt = new ProcessRunner(liftweb, getCommand(launcherJar));
    }

    private static String[] getCommand(File launcherJar) {
        // http://www.assembla.com/wiki/show/liftweb/Using_SBT

        String[] result = null;
        /*
        To avoid frequent OutOfMemory errors, try modifying your sbt shell script to the following:
        java -XX:+CMSClassUnloadingEnabled -XX:MaxPermSize=256m -Xmx512M -Xss2M -jar `dirname $0`/sbt-launch.jar "$@"
         */
        try {
            String jrebelJar = new File(".").getCanonicalPath() + "\\jrebel\\jrebel.jar";
            if (new File(jrebelJar).exists())
            {
                result = new String[]{
                        "java",
                        "-noverify",
                        "-javaagent:" + new File(".").getCanonicalPath() + "\\jrebel\\jrebel.jar",
                        //"-Drebel.log4j-plugin=false",
                         "-XX:+CMSClassUnloadingEnabled",
                         "-XX:MaxPermSize=256m",
                        "-Xmx512M",
                        "-Xss2M",
                        "-Dsbt.log.noformat=true",
                        "-Djline.terminal=jline.UnsupportedTerminal",
                        "-jar", launcherJar.getAbsolutePath()
                };
            } else
            {
                result = new String[]{
                        "java",
                         "-XX:+CMSClassUnloadingEnabled",
                         "-XX:MaxPermSize=256m",
                        "-Xmx512M",
                        "-Xss2M",
                        "-Dsbt.log.noformat=true",
                        "-Djline.terminal=jline.UnsupportedTerminal",
                        "-jar", launcherJar.getAbsolutePath()
                };
            }
        } catch (IOException e) {
        }
        return result;
    }

    public OutputReader subscribeToOutput() {
    //    return output;
        return sbt.subscribeToOutput();
    }

    OutputReader output = null;

    public void start() throws IOException {
        // TODO: detect if the directory does not have a project
        output = sbt.subscribeToOutput();
        sbt.start();
        sbt.destroyOnShutdown();
        output.waitForOutput(PROMPT);
        output.close();
    }

    public void destroy() {
        sbt.destroy();
    }

    public boolean isAlive() {
        return sbt.isAlive();
    }

    public void execute(String action) throws IOException {
        OutputReader output = sbt.subscribeToOutput();
        sbt.writeInput(action + "\n");

        if (action.trim().equals("")) {
            output.waitForOutput(PROMPT_AFTER_EMPTY_ACTION);
        } else {
            output.waitForOutput(PROMPT);
        }
        output.close();
    }
}
